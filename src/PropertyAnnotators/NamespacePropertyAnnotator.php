<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SESP\DatabaseLogReader;
use SMWDataItem as DataItem;
use SMWDIString as DIString;
use SMW\DIProperty;
use SMW\SemanticData;
use Title;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 */
class NamespacePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___NAMESPACE';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var Integer|null
	 */
	private $namespace;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param Title $namespace
	 */
	public function setNamespace( Title $namespace ) {
		$this->namespace = $namespace;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		if ( $this->namespace === null ) {
			$title = $semanticData->getSubject()->getTitle();
			$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();

			$this->namespace = $nsInfo->getCanonicalName( $title->getNamespace() );
			if ( "" === $this->namespace ) {
				$this->namespace = "Main";
			}
		}

		$dataItem = $this->getDataItem();

		if ( $dataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		} else {
			$semanticData->removeProperty( $property );
		}
	}

	private function getDataItem() {
		return new DIString( $this->namespace );
	}

}
