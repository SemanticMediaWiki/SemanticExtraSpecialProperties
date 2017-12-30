<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDITime as DITime;
use SESP\PropertyAnnotator;
use SESP\AppFactory;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 */
class ApprovedDatePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___APPROVEDDATE';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var Integer|null
	 */
	private $approvedDate;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param Integer $approvedDate
	 */
	public function setApprovedDate( $approvedDate ) {
		$this->approvedDate = $approvedDate;
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
	public function addAnnotation(
		DIProperty $property, SemanticData $semanticData
	) {
		if ( $this->approvedDate === null && class_exists( 'ApprovedRevs' ) ) {
			$logReader = $this->appFactory->newDatabaseLogReader(
				$semanticData->getSubject()->getTitle(), 'approval'
			);
			$this->approvedDate = $logReader->getDate();
		}

		$dataItem = null;
		if ( $this->approvedDate ) {
			$date = $this->approvedDate;
			$dataItem = new DITime(
				DITime::CM_GREGORIAN,
				$date->format( 'Y' ),
				$date->format( 'm' ),
				$date->format( 'd' ),
				$date->format( 'H' ),
				$date->format( 'i' )
			);
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		} else {
			$semanticData->removeProperty( $property );
		}
	}
}
