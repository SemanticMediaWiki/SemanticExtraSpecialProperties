<?php

namespace SESP;

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
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 * @author rotsee
 */
class ExtraPropertyAnnotator extends BaseAnnotator {

	/** @var SemanticData */
	protected $semanticData = null;

	protected $configuration = null;
	protected $dbConnection = null;
	protected $page = null;

	/**
	 * @since 0.3
	 *
	 * @param SemanticData $semanticData
	 * @param array $configuration
	 */
	public function __construct( SemanticData $semanticData, array $configuration ) {
		$this->semanticData = $semanticData;
		$this->configuration = $configuration;
	}

	/**
	 * @since 0.3
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
	 * @since 0.3
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
	}

	/**
	 * @since 0.3
	 *
	 * @return WikiPage
	 */
	public function getWikiPage() {

		if ( $this->page === null ) {
			$this->page = $this->loadRegisteredObject( 'WikiPage' );
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

		// _REVID was incorrect in the original SESP because getId returns the
		// page id not the revision Id

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
			case '_METADATA' :
			case '_EXIFDATETIME' :
			case '_EXIFSOFTWARE' :
				$this->addPropertyValuesForExifData();
				break;
			case '_SHORTURL' :
				$this->addPropertyValuesForShortUrl();
				break;
		}

		return $dataItem;
	}

	private function acquireDBConnection() {

		if ( $this->dbConnection === null ) {
			$this->dbConnection = $this->loadRegisteredObject( 'DBConnection', 'DatabaseBase' );
		}

		return $this->dbConnection;
	}

	private function isUserPage() {
		return $this->getWikiPage()->getTitle()->inNamespace( NS_USER );
	}

	private function isImagePage() {
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

		$user = User::newFromId( $this->getWikiPage()->getUser() );
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
		return $this->acquireDBConnection()->estimateRowCount(
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

		if ( $this->isImagePage() ) {

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
		if ( $this->isImagePage() ) {
			$imageMetadataBuilder = new ExifAnnotator( $this->getSemanticData() );
			$imageMetadataBuilder->addAnnotation();
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

		$user = $this->loadRegisteredObject( 'UserByPageName', 'User' );

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
