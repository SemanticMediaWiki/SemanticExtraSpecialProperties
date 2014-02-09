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
use Closure;
use RuntimeException;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 */
class PredefinedPropertyAnnotator {

	/** @var array */
	protected $store = null;
	protected $semanticData = null;
	protected $configuration = null;
	protected $page = null;
	protected $readConnection = null;
	protected $container = array();

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
	 * @param string $objectName
	 * @param Closure $objectSignature
	 */
	public function registerObject( $objectName, Closure $objectSignature ) {
		$this->container[$objectName] = $objectSignature;
	}

	/**
	 * @since 0.3
	 *
	 * @return boolean
	 * @throws RuntimeException
	 */
	public function addAnnotation() {

		if ( !isset( $this->configuration['sespSpecialProperties'] ) || !is_array( $this->configuration['sespSpecialProperties'] ) ) {
			throw new RuntimeException( "Expected a 'sespSpecialProperties' configuration entry" );
		}

		$this->addPropertyValues();
		return true;
	}

	/**
	 * @since 0.3
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
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
			case '_REVID' :
				$dataItem = new DINumber( $this->getWikiPage()->getId() );
				break;
			case '_NREV' :
				$dataItem = new DINumber( $this->getNumberOfRevisions() );
				break;
			case '_NTREV' :
				$dataItem = new DINumber( $this->getNumberOfTalkPageRevisions() );
				break;
			case '_EUSER' :
				$this->addPropertyValuesForPageContributors( $property );
				break;
			case '_SUBP' :
				$this->addPropertyValuesForSubPages( $property );
				break;
			case '_MIMETYPE' :
				// 0.3 use SMW's _MIME;
				// 0.3 use SMW's _MEDIA;
				break;
			case '_METADATA' :
				$this->addPropertyValuesForImageMetadata();
				break;
			case '_SHORTURL' :
				$this->addPropertyValuesForShortUrl();
				break;
		}

		return $dataItem;
	}

	protected function acquireDBConnection() {

		if ( $this->readConnection === null ) {
			$this->readConnection = $this->loadObject( 'DatabaseBase' );
		}

		return $this->readConnection;
	}

	protected function isUserPage() {
		return $this->getWikiPage()->getTitle()->inNamespace( NS_USER );
	}

	protected function isImagePage() {
		return $this->getWikiPage()->getTitle()->inNamespace( NS_FILE );
	}

	protected function getWikiPage() {

		if ( $this->page === null ) {
			$this->page = $this->loadObject( 'WikiPage' );
		}

		return $this->page;
	}

	private function makeFirstAuthorDataItem() {
		if ( $this->getWikiPage()->getCreator() ) {
			return DIWikiPage::newFromTitle( $this->getWikiPage()->getCreator()->getUserPage() );
		}
	}

	private function makeNumberOfPageViewsDataItem() {
		if ( !$this->configuration['wgDisableCounters'] ) {
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

	private function getPageRevisionsForId( $pageId ) {
		return $this->acquireDBConnection()->estimateRowCount(
			"revision",
			"*",
			array( "rev_page" => $pageId )
		);
	}

	private function getNumberOfRevisions() {
		return $this->getPageRevisionsForId( $this->getWikiPage()->getTitle()->getArticleID() );
	}

	private function getNumberOfTalkPageRevisions() {
		return $this->getPageRevisionsForId( $this->getWikiPage()->getTitle()->getTalkPage()->getArticleID() );
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

	private function addPropertyValuesForImageMetadata() {
		if ( $this->isImagePage() ) {
			$imageMetadataBuilder = new ImageMetadataAnnotator( $this->getSemanticData() );
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

		$user = User::newFromName( $this->getWikiPage()->getTitle()->getText() );

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

	private function loadObject( $objectName ) {
		$instance = isset( $this->container[$objectName] ) ? $this->container[$objectName]( $this ) : null;

		if ( $instance instanceof $objectName ) {
			return $instance;
		}

		throw new RuntimeException( "Expected a service object with a {$objectName} signature" );
	}

}
