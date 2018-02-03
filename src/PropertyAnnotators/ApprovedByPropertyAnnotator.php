<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SESP\DatabaseLogReader;
use SMW\DIWikiPage;
use SMWDataItem as DataItem;
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
class ApprovedByPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___APPROVEDBY';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var Integer|null
	 */
	private $approvedBy;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * @param User $approvedBy
	 */
	public function setApprovedBy( $approvedBy ) {
		$this->approvedBy = $approvedBy;
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

		if ( $this->approvedBy === null && class_exists( 'ApprovedRevs' ) ) {
			$logReader = $this->appFactory->newDatabaseLogReader(
				$semanticData->getSubject()->getTitle(),
				'approval'
			);

			$this->approvedBy = $logReader->getUserForLogEntry();
		}

		$dataItem = $this->getDataItem();

		if ( $dataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		} else {
			$semanticData->removeProperty( $property );
		}
	}

	private function getDataItem() {
		if ( $this->approvedBy instanceof User ) {
			$userPage = $this->approvedBy->getUserPage();

			if ( $userPage instanceof Title ) {
				return DIWikiPage::newFromTitle( $userPage );
			}
		}
	}

}
