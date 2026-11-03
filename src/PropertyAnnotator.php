<?php

namespace SESP;

use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
interface PropertyAnnotator {

	/**
	 * @since 2.0
	 *
	 * @param Property $property
	 *
	 * @return bool
	 */
	public function isAnnotatorFor( Property $property );

	/**
	 * `ExtraPropertyAnnotator` only calls an annotator for a subject that has a title that can exist as
	 * a page, and skips every other subject (no title, virtual namespaces, special pages). An
	 * implementation can therefore rely on `$semanticData->getSubject()->getTitle()` not being null.
	 *
	 * @since 2.0
	 *
	 * @param Property $property
	 * @param SemanticData $semanticData
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData );

}
