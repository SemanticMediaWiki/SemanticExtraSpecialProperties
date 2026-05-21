<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Number;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 */
class NamespacePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___NSID';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
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
		$dataItem = new Number( $semanticData->getSubject()->getTitle()->getNamespace() );
		$semanticData->addPropertyObjectValue( $property, $dataItem );
	}
}
