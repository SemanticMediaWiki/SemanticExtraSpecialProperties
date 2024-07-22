<?php

namespace SESP\PropertyAnnotators;

use ApprovedRevs;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDINumber as DINumber;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
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
	 * @var int|null
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
	 * @param int $approvedRev
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

	/**
	 * get data item
	 */
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
