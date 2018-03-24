<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDIBlob as DIBlob;
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
class UserBlockPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___USERBLOCK';

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

		$user = $this->appFactory->newUserFromTitle(
			$title
		);

		if ( !$user instanceof User || ( $block = $user->getBlock() ) === null ) {
			return;
		}

		$actions = [
			'edit',
			'createaccount',
			'sendemail',
			'editownusertalk',
			'read'
		];

		foreach ( $actions as $action ) {
			if ( $block->prevents( $action ) ) {
				$semanticData->addPropertyObjectValue( $property, new DIBlob( $action ) );
			}
		}
	}

}
