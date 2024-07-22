<?php

namespace SESP\PropertyAnnotators;

use ApprovedRevs;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDIString as DIString;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use LogReader;
use Title;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
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
	 * @var int|null
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
			$title = $semanticData->getSubject()->getTitle();
			if ( ApprovedRevs::pageIsApprovable( $title ) ) {
				$approvedRevId = self::getApprovedRevID( $title );
				if ( $approvedRevId !== null ) {
					$latestRevId = $title->getLatestRevID( Title::READ_LATEST );
					if ( $latestRevId === $approvedRevId ) {
						$this->approvedStatus = "approved";
					} else {
						$this->approvedStatus = "pending";
					}
				} else {
					$this->approvedStatus = "unapproved";
				}
			}
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

	private static function getApprovedRevID( $title ): ?int {
		$id = ApprovedRevs::getApprovedRevID( $title );
		return $id === null || $id === false ? null : (int)$id;
	}
}
