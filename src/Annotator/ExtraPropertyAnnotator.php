<?php

namespace SESP\Annotator;

use SESP\PropertyRegistry;
use SESP\DIC\ObjectFactory;

use SMW\SemanticData;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\Store;

use SMWDataItem as DataItem;
use SMWDIBlob as DIBlob;
use SMWDIBoolean as DIBoolean;
use SMWDITime as DITime;
use SMWDINumber as DINumber;

use WikiPage;
use User;
use RuntimeException;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 * @author rotsee
 */
class ExtraPropertyAnnotator {

	/** @var SemanticData */
	protected $semanticData = null;

	protected $configuration = null;
	protected $dbConnection = null;
	protected $page = null;

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 * @param array $configuration
	 */
	public function __construct( SemanticData $semanticData, array $configuration ) {
		$this->semanticData = $semanticData;
		$this->configuration = $configuration;
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 * @throws RuntimeException
	 */
	public function addAnnotation() {

		if ( isset( $this->configuration['sespSpecialProperties'] ) &&
			is_array( $this->configuration['sespSpecialProperties'] ) ) {
			return $this->addPropertyValues();
		}

		throw new RuntimeException( "Expected a 'sespSpecialProperties' configuration array" );
	}

	/**
	 * @since 1.0
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
	}

	protected function getWikiPage() {

		if ( $this->page === null ) {
			$this->page = ObjectFactory::getInstance()->newWikiPage(
				$this->getSemanticData()->getSubject()->getTitle()
			);
		}

		return $this->page;
	}

	protected function addPropertyValues() {

		$cachedProperties = array();

		foreach ( $this->configuration['sespSpecialProperties'] as $externalId ) {

			$propertyId = PropertyRegistry::getInstance()->getPropertyId( $externalId );

			if ( $this->hasRegisteredPropertyId( $propertyId, $cachedProperties ) ) {
				continue;
			}

			$propertyDI = new DIProperty( $propertyId );

			if ( $this->getSemanticData()->getPropertyValues( $propertyDI ) !== array() ) {
				$cachedProperties[ $propertyId ] = true;
				continue;
			}

			$dataItem = $this->createDataItemById( $externalId, $propertyDI );

			if ( $dataItem instanceof DataItem ) {
				$cachedProperties[ $propertyId ] = true;
				$this->getSemanticData()->addPropertyObjectValue( $propertyDI, $dataItem );
			}

		}

		return true;
	}

	protected function hasRegisteredPropertyId( $propertyId, $cachedProperties ) {
		return ( DIProperty::getPredefinedPropertyTypeId( $propertyId ) === '' ) ||
			array_key_exists( $propertyId, $cachedProperties );
	}

	protected function createDataItemById( $externalId, $property ) {

		$dataItem = null;

		switch ( $externalId ) {
			case '_CUSER' :
				$dataItem = $this->makeFirstAuthorDataItem();
				break;
			case '_VIEWS' :
				$dataItem = $this->makeNumberOfPageViewsDataItem();
				break;
			case '_USERREG' :
				$dataItem = $this->makeUserRegistrationDataItem();
				break;
			case '_PAGEID' :
				$dataItem = $this->makePageIdDataItem();
				break;
			case '_REVID' :
				$dataItem = $this->makeRevisionIdDataItem();
				break;
			case '_NREV' :
				$dataItem = $this->makeNumberOfRevisionsDataItem();
				break;
			case '_NTREV' :
				$dataItem = $this->makeNumberOfTalkPageRevisionsDataItem();
				break;
			case '_EUSER' :
				$this->addPropertyValuesForPageContributors( $property );
				break;
			case '_SUBP' :
				$this->addPropertyValuesForSubPages( $property );
				break;
			case '_MEDIATYPE' :
			case '_MIMETYPE' :
				$this->addPropertyValuesForMIMEAndMediaType();
				break;
			case '_EXIFDATA' :
				$this->addPropertyValuesForExifData();
				break;
			case '_SHORTURL' :
				$this->addPropertyValuesForShortUrl();
				break;
		}

		return $dataItem;
	}

	private function getDBConnection() {
		return ObjectFactory::getInstance()->getDBConnection( DB_SLAVE );
	}

	private function isUserPage() {
		return $this->getWikiPage()->getTitle()->inNamespace( NS_USER );
	}

	private function isFilePage() {
		return $this->getWikiPage()->getTitle()->inNamespace( NS_FILE );
	}

	private function makeFirstAuthorDataItem() {
		$creator = $this->getWikiPage()->getCreator();

		if ( $creator ) {
			return DIWikiPage::newFromTitle( $creator->getUserPage() );
		}
	}

	private function makeNumberOfPageViewsDataItem() {
		if ( !$this->configuration['wgDisableCounters'] && $this->getWikiPage()->getCount() ) {
			return new DINumber( $this->getWikiPage()->getCount() );
		}
	}

	private function addPropertyValuesForPageContributors( DIProperty $property ) {

		$user = ObjectFactory::getInstance()->newUserFromId( $this->getWikiPage()->getUser() );

		$authors = $this->getWikiPage()->getContributors();

		while ( $user ) {
			if ( !( in_array( 'bot', $user->getRights() ) &&
				$this->configuration['wgSESPExcludeBots'] ) &&
				!$user->isAnon() ) { //no anonymous users (hidden users are not returned)

				$this->getSemanticData()->addPropertyObjectValue(
					$property,
					DIWikiPage::newFromTitle( $user->getUserPage() )
				);
			}

			$user = $authors->current();
			$authors->next();
		}
	}

	private function makePageIdDataItem() {
		$revId = $this->getWikiPage()->getId();

		if ( is_integer( $revId ) ) {
			return new DINumber( $revId );
		}
	}

	private function makeRevisionIdDataItem() {
		$revId = $this->getWikiPage()->getLatest();

		if ( is_integer( $revId ) ) {
			return new DINumber( $revId );
		}
	}

	private function getPageRevisionsForId( $pageId ) {
		return $this->getDBConnection()->estimateRowCount(
			"revision",
			"*",
			array( "rev_page" => $pageId )
		);
	}

	private function makeNumberOfRevisionsDataItem() {
		return new DINumber( $this->getPageRevisionsForId(
			$this->getWikiPage()->getTitle()->getArticleID()
		) );
	}

	private function makeNumberOfTalkPageRevisionsDataItem() {
		$numberOfPageRevisions = $this->getPageRevisionsForId(
			$this->getWikiPage()->getTitle()->getTalkPage()->getArticleID()
		);

		if ( $this->getWikiPage()->getTitle()->getTalkPage()->exists() && $numberOfPageRevisions > 0 ) {
			return new DINumber( $numberOfPageRevisions );
		}
	}

	private function addPropertyValuesForMIMEAndMediaType(){

		if ( $this->isFilePage() ) {

			$file = $this->getWikiPage()->getFile();
			$mimetype = $file->getMimeType();
			$mediaType = \MimeMagic::singleton()->findMediaType( $mimetype );
			list( $mimetypemajor, $mimetypeminor ) = $file->splitMime( $mimetype );

			$this->getSemanticData()->addPropertyObjectValue(
				new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '_MIMETYPE' ) ),
				new DIBlob( $mimetypeminor )
			);

			$this->getSemanticData()->addPropertyObjectValue(
				new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '_MEDIATYPE' ) ),
				new DIBlob( $mediaType )
			);
		}

	}

	private function addPropertyValuesForSubPages( DIProperty $property ) {

		//-1 = no limit. Returns TitleArray object
		$subpages = $this->getWikiPage()->getTitle()->getSubpages ( -1 );

		foreach ( $subpages as $title ) {
			$this->getSemanticData()->addPropertyObjectValue(
				$property,
				DIWikiPage::newFromTitle( $title )
			);
		}
	}

	private function addPropertyValuesForExifData() {
		if ( $this->isFilePage() ) {
			$exifDataAnnotator = new ExifDataAnnotator( $this->getSemanticData() );
			$exifDataAnnotator->setFile( $this->getWikiPage()->getFile() );
			$exifDataAnnotator->addAnnotation();
		}
	}

	private function addPropertyValuesForShortUrl() {
		if ( class_exists( 'ShortUrlUtils' ) ) {
			$shortUrlAnnotator = new ShortUrlAnnotator( $this->getSemanticData(), $this->configuration );
			$shortUrlAnnotator->addAnnotation();
		}
	}

	private function makeUserRegistrationDataItem() {

		if ( !$this->isUserPage() ) {
			return null;
		}

		$user = ObjectFactory::getInstance()->newUserFromName( $this->getWikiPage()->getTitle() );

		if ( $user instanceof User ) {

			$timestamp = wfTimestamp( TS_ISO_8601, $user->getRegistration() );
			$date = new \DateTime( $timestamp );

			return new DITime(
				DITime::CM_GREGORIAN,
				$date->format('Y'),
				$date->format('m'),
				$date->format('d'),
				$date->format('H'),
				$date->format('i')
			);
		}
	}

}
