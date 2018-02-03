<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDIString as DIString;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use LogReader;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 */
class ApprovedStatusPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___APPROVEDSTATUS';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var Integer|null
	 */
	private $approvedStatus;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param string $approvedStatus
	 */
	public function setApprovedStatus( $approvedStatus ) {
		$this->approvedStatus = $approvedStatus;
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

		if ( $this->approvedStatus === null && class_exists( 'ApprovedRevs' ) ) {

			$logReader = $this->appFactory->newDatabaseLogReader(
				$semanticData->getSubject()->getTitle(), 'approval'
			);

			$this->approvedStatus = $logReader->getStatusOfLogEntry();
		}

		if ( is_string( $this->approvedStatus ) && $this->approvedStatus !== '' ) {
			$semanticData->addPropertyObjectValue(
				$property,
				new DIString( $this->approvedStatus )
			);
		} else {
			$semanticData->removeProperty( $property );
		}
	}

}
