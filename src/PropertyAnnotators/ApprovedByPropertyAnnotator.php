<?php

namespace SESP\PropertyAnnotators;

use ApprovedRevs;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentity;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

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
	public const PROP_ID = '___APPROVEDBY';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var UserIdentity|false|null
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
	 * @param UserIdentity|false|null $approvedBy
	 */
	public function setApprovedBy( $approvedBy ) {
		$this->approvedBy = $approvedBy;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
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
		$approver = $this->approvedBy;

		// ApprovedRevs declares the approver as a UserIdentity, which is not necessarily a User.
		if ( $approver instanceof UserIdentity && !$approver instanceof User ) {
			$approver = User::newFromIdentity( $approver );
		}

		if ( $approver instanceof User ) {
			$userPage = $approver->getUserPage();

			if ( $userPage instanceof Title ) {
				return WikiPage::newFromTitle( $userPage );
			}
		}
	}

}
