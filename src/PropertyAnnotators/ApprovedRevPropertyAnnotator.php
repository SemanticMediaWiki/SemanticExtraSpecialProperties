<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDINumber as DINumber;
use SMWDIBoolean as DIBool;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use ApprovedRevs;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 */
class ApprovedRevPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___APPROVED';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
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
		if ( !class_exists( 'ApprovedRevs' ) ) {
			return null;
		}

		$title = $semanticData->getSubject()->getTitle();
		$rev = ApprovedRevs::getApprovedRevID( $title );

		if ( is_numeric( $rev ) ) {
			$semanticData->addPropertyObjectValue( $property, new DINumber( $rev ) );
		} else {
			$semanticData->removeProperty( $property );
		}
	}
}
