<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDIString as DIString;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use ParserOptions;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageDescriptionPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___DESCRIPTION';

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

		$page = $this->appFactory->newWikiPage( $semanticData->getSubject()->getTitle() );
		$description = $this->getPageDescription( $page );

		if ( !empty( $description ) ) {
			$semanticData->addPropertyObjectValue( $property, new DIString( $description ) );
		}
	}

	private function getPageDescription( $page ) {

		$parser =  $page->getParserOutput( ParserOptions::newCanonical( 'canonical' ) );
		$description = $parser->getProperty( 'description' );

		if ( $description !== false ) { // set by Description2 extension, install it if you want proper og:description support
			return $description;
		}

		return null;
	}

}
