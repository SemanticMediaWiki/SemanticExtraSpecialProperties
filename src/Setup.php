<?php

namespace SESP;

use SESP\PropertyRegistry;
use SESP\Annotator\ExtraPropertyAnnotator;
use SESP\DIC\ObjectFactory;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.2.0
 *
 * @author mwjames
 */
class Setup {

	/** @var array */
	protected $globalVars;
	protected $rootDirectory;

	/**
	 * @since 1.2.0
	 *
	 * @param array $language
	 * @param string $rootDirectory
	 */
	public function __construct( array &$globalVars, $rootDirectory ) {
		$this->globalVars =& $globalVars;
		$this->rootDirectory = $rootDirectory;
	}

	/**
	 * @since 1.2.0
	 *
	 * @return boolean
	 */
	public function run() {
		$this->init();
		$this->registerMessageFiles();
		$this->registerHooksInDeferredMode();
	}

	protected function init() {

		if ( !isset( $this->globalVars['sespCacheType'] ) ) {
			$this->globalVars['sespCacheType'] = CACHE_ANYTHING;
		}
	}

	protected function registerMessageFiles() {
		$this->globalVars['wgMessagesDirs']['semantic-extra-special-properties'] = $this->rootDirectory . '/i18n';
		$this->globalVars['wgExtensionMessagesFiles']['semantic-extra-special-properties'] = $this->rootDirectory . '/SemanticExtraSpecialProperties.i18n.php';
	}

	protected function registerHooksInDeferredMode() {

		// FIXME PHP 5.3 doesn't allow to use $this reference within a closure
		// when 5.3 is obsolete use $this instead (PHP 5.4+)
		$globalVars = $this->globalVars;

		$this->globalVars['wgExtensionFunctions']['semantic-extra-special-properties'] = function( $reporter = null ) use( $globalVars ) {

			/**
			 * Collect only relevant configuration parameters
			 *
			 * @since 1.0
			 */
			$configuration = array(
				'wgDisableCounters'     => $globalVars['wgDisableCounters'],
				'sespUseAsFixedTables'  => isset( $globalVars['sespUseAsFixedTables'] ) ? $globalVars['sespUseAsFixedTables']  : false,
				'sespSpecialProperties' => isset( $globalVars['sespSpecialProperties'] ) ? $globalVars['sespSpecialProperties'] : array(),
				'wgSESPExcludeBots'     => isset( $globalVars['wgSESPExcludeBots'] ) ? $globalVars['wgSESPExcludeBots'] : false,
				'wgShortUrlPrefix'      => isset( $globalVars['wgShortUrlPrefix'] )  ? $globalVars['wgShortUrlPrefix']  : false
			);

			ObjectFactory::getInstance()->registerObject( 'sesp.configuration', $configuration );

			/**
			 * Register as fixed tables
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['SMW::SQLStore::updatePropertyTableDefinitions'][] = function ( &$propertyTableDefinitions ) use ( $configuration ) {
				return PropertyRegistry::getInstance()->registerAsFixedTables( $propertyTableDefinitions, $configuration );
			};

			/**
			 * Register properties
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['smwInitProperties'][] = function () {
				return PropertyRegistry::getInstance()->registerPropertiesAndAliases();
			};

			/**
			 * Execute and update annotations
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['SMWStore::updateDataBefore'][] = function ( \SMW\Store $store, \SMW\SemanticData $semanticData ) use ( $configuration ) {
				$propertyAnnotator = new ExtraPropertyAnnotator( $semanticData, $configuration );
				return $propertyAnnotator->addAnnotation();
			};

			return true;
		};
	}

}
