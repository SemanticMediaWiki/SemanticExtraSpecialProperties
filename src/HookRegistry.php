<?php

namespace SESP;

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
	private $handlers = [];

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

	/**
	 * @since  1.4
	 *
	 * @param array &$config
	 */
	public static function onBeforeConfigCompletion( &$config ) {

		$exemptionlist = [
			'___EUSER', '___CUSER', '___SUBP', '___REVID', '___VIEWS',
			'___NREV', '___NTREV', '___USEREDITCNT', '___EXIFDATA'
		];

		// Exclude listed properties from indexing
		if ( isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
			$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
				$config['smwgFulltextSearchPropertyExemptionList'],
				$exemptionlist
			);
		}

		// Exclude listed properties from dependency detection as each of the
		// selected object would trigger an automatic change without the necessary
		// human intervention and can therefore produce unwanted query updates
		if ( isset( $config['smwgQueryDependencyPropertyExemptionlist'] ) ) {
			$config['smwgQueryDependencyPropertyExemptionlist'] = array_merge(
				$config['smwgQueryDependencyPropertyExemptionlist'],
				$exemptionlist
			);
		}
	}

	private function registerCallbackHandlers( $configuration ) {

		$applicationFactory = \SMW\ApplicationFactory::getInstance();

		$appFactory = new AppFactory(
			$configuration,
			$applicationFactory->getCache()
		);

		$appFactory->setLogger(
			$applicationFactory->getMediaWikiLogger( 'sesp' )
		);

		$propertyRegistry = new PropertyRegistry(
			$appFactory
		);

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
		 */
		$this->handlers['SMW::Property::initProperties'] = function ( $basePropertyRegistry ) use ( $propertyRegistry ) {

			$propertyRegistry->registerOn(
				$basePropertyRegistry
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::AddCustomFixedPropertyTables
		 */
		$this->handlers['SMW::SQLStore::AddCustomFixedPropertyTables'] = function( array &$customFixedProperties, &$fixedPropertyTablePrefix ) use( $propertyRegistry ) {

			$propertyRegistry->registerAsFixedProperties(
				$customFixedProperties,
				$fixedPropertyTablePrefix
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMWStore::updateDataBefore
		 */
		$this->handlers['SMWStore::updateDataBefore'] = function ( $store, $semanticData ) use ( $appFactory ) {

			$extraPropertyAnnotator = new ExtraPropertyAnnotator(
				$appFactory
			);

			$extraPropertyAnnotator->addAnnotation( $semanticData );

			return true;
		};
	}

}
