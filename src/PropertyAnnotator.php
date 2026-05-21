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
	 * @since 2.0
	 *
	 * @param Property $property
	 * @param SemanticData $semanticData
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData );

}
