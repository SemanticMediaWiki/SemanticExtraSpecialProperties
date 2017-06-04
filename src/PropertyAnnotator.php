<?php

namespace SESP;

use SMW\DIProperty;
use SMW\SemanticData;

/**
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
interface PropertyAnnotator {

	/**
	 * @since 2.0
	 *
	 * @param DIProperty $property
	 *
	 * @return boolean
	 */
	public function isAnnotatorFor( DIProperty $property );

	/**
	 * @since 2.0
	 *
	 * @param DIProperty $property
	 * @param SemanticData $semanticData
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData );

}
