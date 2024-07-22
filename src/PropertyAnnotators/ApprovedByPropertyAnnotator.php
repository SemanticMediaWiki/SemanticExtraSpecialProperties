<?php

namespace SESP\PropertyAnnotators;

use ApprovedRevs;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use Title;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
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
	 * @var int|null
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
			$title = $semanticData->getSubject()->getTitle();
			if ( ApprovedRevs::pageIsApprovable( $title ) ) {
				$this->approvedBy = ApprovedRevs::getRevApprover( $title );
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
		if ( $this->approvedBy instanceof User ) {
			$userPage = $this->approvedBy->getUserPage();

			if ( $userPage instanceof Title ) {
				return DIWikiPage::newFromTitle( $userPage );
			}
		}
	}

}
