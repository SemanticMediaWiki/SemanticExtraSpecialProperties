<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SESP\PropertyAnnotator;
use SESP\AppFactory;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class CreatorPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___CUSER';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @since 2.0
	 *
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {

		$page = $this->appFactory->newWikiPage( $semanticData->getSubject()->getTitle() );
		$creator = $page->getCreator();
		$dataItem = null;

		if ( $creator ) {
			$dataItem = DIWikiPage::newFromTitle( $creator->getUserPage() );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
