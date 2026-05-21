<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;
use SMW\DataItems\DataItem;
use SMW\DataItems\Time;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class UserRegistrationDatePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___USERREG';

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
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$title = $semanticData->getSubject()->getTitle();

		if ( !$title->inNamespace( NS_USER ) ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle( $title );
		$dataItem = null;

		if ( $user instanceof User ) {

			$timestamp = wfTimestamp( TS_ISO_8601, $user->getRegistration() );
			$date = new \DateTime( $timestamp );

			$dataItem = new Time(
				Time::CM_GREGORIAN,
				$date->format( 'Y' ),
				$date->format( 'm' ),
				$date->format( 'd' ),
				$date->format( 'H' ),
				$date->format( 'i' )
			);
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
