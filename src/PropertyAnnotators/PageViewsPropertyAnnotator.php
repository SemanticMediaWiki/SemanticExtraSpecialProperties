<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDINumber as DINumber;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageViewsPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___VIEWS';

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
		if ( $this->appFactory->getOption( 'wgDisableCounters' ) ) {
			return null;
		}

		$page = $this->appFactory->newWikiPage( $semanticData->getSubject()->getTitle() );
		$count = $this->getPageViewCount( $page );

		if ( is_numeric( $count ) ) {
			$semanticData->addPropertyObjectValue( $property, new DINumber( $count ) );
		}
	}

	private function getPageViewCount( $page ) {
		if ( class_exists( '\HitCounters\HitCounters' ) ) {
			return \HitCounters\HitCounters::getCount( $page->getTitle() );
		}

		if ( method_exists( $page, 'getCount' ) ) {
			return $page->getCount();
		}

		return null;
	}

}
