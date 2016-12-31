<?php

namespace SESP;

use SESP\PropertyRegistry;
use SESP\Annotator\ExtraPropertyAnnotator;
use SESP\Annotator\ShortUrlAnnotator;
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

	/**
	 * @since  1.4
	 *
	 * @param array &$config
	 */
	public static function onBeforeConfigCompletion( &$config ) {

		// Exclude listed properties from indexing
		if ( isset( $config['smwgFulltextSearchPropertyExemptionList'] ) ) {
			$config['smwgFulltextSearchPropertyExemptionList'] = array_merge(
				$config['smwgFulltextSearchPropertyExemptionList'],
				array(
					'___EUSER', '___CUSER', '___SUBP', '___REVID', '___VIEWS',
					'___NREV', '___NTREV', '___USEREDITCNT', '___EXIFDATA'
				)
			);
		}

		// Exclude listed properties from dependency detection as each of the
		// selected object would trigger an automatic change without the necessary
		// human intervention and can therefore produce unwanted query updates
		if ( isset( $config['smwgQueryDependencyPropertyExemptionlist'] ) ) {
			$config['smwgQueryDependencyPropertyExemptionlist'] = array_merge(
				$config['smwgQueryDependencyPropertyExemptionlist'],
				array(
					'___REVID', '___VIEWS', '___NREV', '___NTREV',
					'___USEREDITCNT', '___EXIFDATA'
				)
			);
		}
	}

	private function registerCallbackHandlers( $configuration ) {

		$this->handlers['smwInitProperties'] = function () {
			return PropertyRegistry::getInstance()->registerPropertiesAndAliases();
		};

		$this->handlers['SMW::SQLStore::updatePropertyTableDefinitions'] = function ( &$propertyTableDefinitions ) use( $configuration ) {
			return PropertyRegistry::getInstance()->registerAsFixedTables( $propertyTableDefinitions, $configuration );
		};

		$this->handlers['SMWStore::updateDataBefore'] = function ( $store, $semanticData ) use ( $configuration ) {

			$appFactory = new AppFactory( $configuration['wgShortUrlPrefix'] );

			$propertyAnnotator = new ExtraPropertyAnnotator(
				$semanticData,
				$appFactory,
				$configuration
			);

			return $propertyAnnotator->addAnnotation();
		};
	}

}
