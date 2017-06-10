<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
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
class TalkPageNumRevisionPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___NTREV';

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

		$title = $semanticData->getSubject()->getTitle()->getTalkPage();

		$numRevisions = $this->getPageRevisions(
			$title->getArticleID()
		);

		$dataItem = null;

		if ( $title->exists() && $numRevisions > 0 ) {
			$dataItem = new DINumber( $numRevisions );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

	private function getPageRevisions( $pageId ) {
		return $this->appFactory->getConnection()->estimateRowCount(
			"revision",
			"*",
			[ "rev_page" => $pageId ]
		);
	}

}
