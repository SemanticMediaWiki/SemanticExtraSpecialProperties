<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDIBlob as DIBlob;
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
class UserGroupPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___USERGROUP';

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

		if ( !$user instanceof User ) {
			return;
		}

		foreach ( $user->getGroups() as $group ) {
			$semanticData->addPropertyObjectValue( $property, new DIBlob( $group ) );
		}
	}

}
