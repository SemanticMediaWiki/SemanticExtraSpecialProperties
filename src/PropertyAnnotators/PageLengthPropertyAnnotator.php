<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDINumber as DINumber;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageLengthPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___PAGELGTH';

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
		$title = $semanticData->getSubject()->getTitle();

		$length = $title->getLength();
		$dataItem = null;

		if ( is_int( $length ) && $length > 0 ) {
			$dataItem = new DINumber( $length );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
