<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\DataItem;
use SMW\DataItems\Number;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageIDPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___PAGEID';

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
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$page = $this->appFactory->newWikiPage(
			$semanticData->getSubject()->getTitle()
		);

		$pageID = $page->getId();
		$dataItem = null;

		if ( is_int( $pageID ) && $pageID > 0 ) {
			$dataItem = new Number( $pageID );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
