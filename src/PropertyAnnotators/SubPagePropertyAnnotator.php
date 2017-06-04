<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDINumber as DINumber;
use SESP\PropertyAnnotator;
use SESP\AppFactory;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class SubPagePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___SUBP';

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

		//-1 = no limit. Returns TitleArray object
		$subpages = $title->getSubpages( -1 );

		foreach ( $subpages as $title ) {
			$semanticData->addPropertyObjectValue(
				$property,
				DIWikiPage::newFromTitle( $title )
			);
		}
	}

}
