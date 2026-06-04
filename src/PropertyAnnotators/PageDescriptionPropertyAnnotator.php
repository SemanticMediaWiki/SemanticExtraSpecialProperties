<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Blob;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * Annotates a page with its description, read from the `description` page
 * property. That property is written by meta-description extensions such as
 * Description2 and WikiSEO; this annotator reads it regardless of the writer
 * and leaves the annotation empty when no such page property is present, so
 * there is no hard dependency on any particular extension.
 *
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class PageDescriptionPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___DESCRIPTION';

	public function __construct(
		private readonly AppFactory $appFactory
	) {
	}

	/**
	 * @since 7.0.0
	 *
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 7.0.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$description = $this->appFactory->getPageProperty(
			$semanticData->getSubject()->getTitle(),
			'description'
		);

		if ( $description !== null && $description !== '' ) {
			$semanticData->addPropertyObjectValue( $property, new Blob( $description ) );
		}
	}

}
