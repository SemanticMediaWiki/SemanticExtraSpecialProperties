<?php

namespace SESP\Annotator;

use SESP\PropertyRegistry;
use SESP\AppFactory;

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

	/**
	 * @var SemanticData
	 */
	protected $semanticData = null;

	/**
	 * @var AppFactory
	 */
	private $appFactory = null;


	protected $configuration = null;
	private $dbConnection = null;
	private $page = null;

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 * @param Factory $factory
	 * @param array $configuration
	 */
	public function __construct( SemanticData $semanticData, AppFactory $appFactory, array $configuration ) {
		$this->semanticData = $semanticData;
		$this->appFactory = $appFactory;
		$this->configuration = $configuration;
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 * @throws RuntimeException
	 *
	 * @return boolean
	 */
	public function addAnnotation() {

		$subject = $this->semanticData->getSubject();

		if ( $subject === null || $subject->getTitle() === null || $subject->getTitle()->isSpecialPage() ) {
			return false;
		}

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

	/**
	 * @since 1.0
	 *
	 * @return WikiPage
	 */
	public function getWikiPage() {

		if ( $this->page === null ) {
			$this->page = $this->appFactory->newWikiPage( $this->semanticData->getSubject()->getTitle() ); //$this->loadRegisteredObject( 'WikiPage' );
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
			case '_USEREDITCNT' :
				$dataItem = $this->makeUserEditCountDataItem();
				break;
			case '_PAGEID' :
				$dataItem = $this->makePageIdDataItem();
				break;
			case '_PAGELGTH' :
				$dataItem = $this->makePageLengthDataItem();
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
		if ( $this->configuration['wgDisableCounters'] ) {
			return null;
		}

		$count = $this->getPageViewCount();

		if ( !is_numeric( $count ) ) {
			return null;
		}

		return new DINumber( $count );
	}

	private function getPageViewCount() {
		if ( class_exists( '\HitCounters\HitCounters' ) ) {
			return \HitCounters\HitCounters::getCount( $this->getWikiPage()->getTitle() );
		}

		if ( method_exists( $this->getWikiPage(), 'getCount' ) ) {
			return $this->getWikiPage()->getCount();
		}

		return null;
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
		$pageID = $this->getWikiPage()->getId();

		if ( is_integer( $pageID ) && $pageID > 0 ) {
			return new DINumber( $pageID );
		}
	}

	private function makePageLengthItem() {
		$pageID = $this->getWikiPage()->getLen();
		
		if ( is_integer( $pageLen ) && $pageLen > 0 ) {
			return new DINumber( $pageLen );
		}
	}
	
	private function makeRevisionIdDataItem() {
		$revID = $this->getWikiPage()->getLatest();

		if ( is_integer( $revID ) && $revID > 0 ) {
			return new DINumber( $revID );
		}
	}

	private function getPageRevisionsForId( $pageId ) {

		if ( $this->dbConnection === null ) {
			$this->dbConnection = $this->appFactory->newDatabaseConnection(); //( 'DBConnection', 'DatabaseBase' );
		}

		return $this->dbConnection->estimateRowCount(
			"revision",
			"*",
			array( "rev_page" => $pageId )
		);
	}

	private function makeNumberOfRevisionsDataItem() {
		$numberOfPageRevisions = $this->getPageRevisionsForId(
			$this->getWikiPage()->getTitle()->getArticleID()
		);

		if ( $this->getWikiPage()->getTitle()->exists() && $numberOfPageRevisions > 0 ) {
			return new DINumber( $numberOfPageRevisions );
		}
	}

	private function makeNumberOfTalkPageRevisionsDataItem() {
		$numberOfTalkPageRevisions = $this->getPageRevisionsForId(
			$this->getWikiPage()->getTitle()->getTalkPage()->getArticleID()
		);

		if ( $this->getWikiPage()->getTitle()->getTalkPage()->exists() && $numberOfTalkPageRevisions > 0 ) {
			return new DINumber( $numberOfTalkPageRevisions );
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
			$this->appFactory->newExifDataAnnotator( $this->getSemanticData(), $this->getWikiPage()->getFile() )->addAnnotation();
		}
	}

	private function addPropertyValuesForShortUrl() {

		$shortUrlAnnotator = $this->appFactory->newShortUrlAnnotator( $this->getSemanticData() );

		if ( $shortUrlAnnotator->canUseShortUrl() ) {
			$shortUrlAnnotator->addAnnotation();
		}
	}

	private function makeUserRegistrationDataItem() {

		if ( !$this->isUserPage() ) {
			return null;
		}

		$user = $this->appFactory->newUserFromTitle( $this->getWikiPage()->getTitle() );

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

	private function makeUserEditCountDataItem() {

		if ( !$this->isUserPage() ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle( $this->getWikiPage()->getTitle() );

		$count = $user instanceof User ? $user->getEditCount() : false;

		if ( $count ) {
			return new DINumber( $count );
		}
	}

}
