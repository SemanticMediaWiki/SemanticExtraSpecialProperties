<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\DataItem;
use SMW\DataItems\Number;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageNumRevisionPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___NREV';

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
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$title = $semanticData->getSubject()->getTitle();

		$numRevisions = $this->getPageRevisions(
			$title->getArticleID()
		);

		$dataItem = null;

		if ( $title->exists() && $numRevisions > 0 ) {
			$dataItem = new Number( $numRevisions );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

	private function getPageRevisions( $pageId ) {
		return $this->appFactory->getConnection()->estimateRowCount(
			"revision",
			"*",
			[ "rev_page" => $pageId ],
			__METHOD__
		);
	}

}
