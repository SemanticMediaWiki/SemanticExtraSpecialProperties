<?php

namespace SESP;

use SESP\PropertyRegistry;
use SESP\Annotator\ExtraPropertyAnnotator;
use SMW\Store;
use SMW\SemanticData;
use Hooks;

/**
 * @license GNU GPL v2+
 * @since 1.3
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var array
	 */
	private $handlers = array();

	/**
	 * @since 1.0
	 *
	 * @param array $configuration
	 */
	public function __construct( $configuration ) {
		$this->registerCallbackHandlers( $configuration );
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 */
	public function deregister() {
		foreach ( array_keys( $this->handlers ) as $name ) {

			Hooks::clear( $name );

			// Remove registered `wgHooks` hooks that are not cleared by the
			// previous call
			if ( isset( $GLOBALS['wgHooks'][$name] ) ) {
				unset( $GLOBALS['wgHooks'][$name] );
			}
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return Hooks::isRegistered( $name );
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public function getHandlers( $name ) {
		return Hooks::getHandlers( $name );
	}

	private function registerCallbackHandlers( $configuration ) {

		$propertyRegistry = PropertyRegistry::getInstance();

		/**
		 * @see ...
		 */
		$this->handlers['smwInitProperties'] = function () use( $propertyRegistry ) {
			return $propertyRegistry->registerPropertiesAndAliases();
		};

		/**
		 * @see ...
		 */
		$this->handlers['SMW::SQLStore::updatePropertyTableDefinitions'] = function ( &$propertyTableDefinitions ) use( $propertyRegistry, $configuration ) {
			return $propertyRegistry->registerAsFixedTables( $propertyTableDefinitions, $configuration );
		};

		/**
		 * @see ...
		 */
		$this->handlers['SMWStore::updateDataBefore'] = function ( Store $store, SemanticData $semanticData ) use ( $configuration ) {

			$propertyAnnotator = new ExtraPropertyAnnotator( $semanticData, $configuration );

			// DI object registration
			$propertyAnnotator->registerObject( 'DBConnection', function() {
				return wfGetDB( DB_SLAVE );
			} );

			$propertyAnnotator->registerObject( 'WikiPage', function( $instance ) {
				return \WikiPage::factory( $instance->getSemanticData()->getSubject()->getTitle() );
			} );

			$propertyAnnotator->registerObject( 'UserByPageName', function( $instance ) {
				return \User::newFromName( $instance->getWikiPage()->getTitle()->getText() );
			} );

			return $propertyAnnotator->addAnnotation();
		};
	}

}
