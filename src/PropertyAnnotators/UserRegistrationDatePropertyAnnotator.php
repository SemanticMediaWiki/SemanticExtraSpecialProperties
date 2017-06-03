<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDITime as DITime;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class UserRegistrationDatePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___USERREG';

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

		if ( !$title->inNamespace( NS_USER ) ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle( $title );
		$dataItem = null;

		if ( $user instanceof User ) {

			$timestamp = wfTimestamp( TS_ISO_8601, $user->getRegistration() );
			$date = new \DateTime( $timestamp );

			$dataItem = new DITime(
				DITime::CM_GREGORIAN,
				$date->format('Y'),
				$date->format('m'),
				$date->format('d'),
				$date->format('H'),
				$date->format('i')
			);
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
