<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDINumber as DINumber;
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
	 * @var Integer|null
	 */
	private $approvedRev;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param Integer $approvedRev
	 */
	public function setApprovedRev( $approvedRev ) {
		$this->approvedRev = $approvedRev;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	public function getDataItem() {
		return new DINumber( $this->approvedRev );
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData	) {

		if ( $this->approvedRev === null && class_exists( 'ApprovedRevs' ) ) {
			$this->approvedRev = ApprovedRevs::getApprovedRevID(
				$semanticData->getSubject()->getTitle()
			);
		}

		if ( is_numeric( $this->approvedRev ) ) {
			$semanticData->addPropertyObjectValue(
				$property,
				$this->getDataItem()
			);
		} else {
			$semanticData->removeProperty( $property );
		}
	}

}
