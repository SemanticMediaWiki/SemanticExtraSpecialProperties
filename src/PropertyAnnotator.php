<?php

namespace SESP;

use SMW\DIProperty;
use SMW\SemanticData;

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
	 * @param DIProperty $property
	 *
	 * @return bool
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
