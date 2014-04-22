<?php

namespace SESP;

use SESP\PropertyRegistry;
use SESP\Annotator\ExtraPropertyAnnotator;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class Setup {

	/** @var array */
	protected $globalVars= array();

	protected $rootDirectory = '';
	protected $reporter = null;

	/**
	 * @since 1.2.0
	 *
	 * @param array $language
	 * @param string $rootDirectory
	 * @param Closure|null $reporter
	 */
	public function __construct( array &$globalVars, $rootDirectory, $reporter = null ) {
		$this->globalVars =& $globalVars;
		$this->rootDirectory = $rootDirectory;
		$this->reporter = $reporter;
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

		$observableReporter = new ObservableReporter;
		$observableReporter->registerCallback( $this->reporter );

		// FIXME PHP 5.3 doesn't allow to use $this reference within a closure
		// when 5.3 is obsolete use $this instead (PHP 5.4+)
		$globalVars = $this->globalVars;

		$this->globalVars['wgExtensionFunctions']['semantic-extra-special-properties'] = function() use( $globalVars, $observableReporter ) {

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

			/**
			 * Register as fixed tables
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['SMW::SQLStore::updatePropertyTableDefinitions'][] = function ( &$propertyTableDefinitions ) use ( $configuration, $observableReporter ) {
				$observableReporter->reportStatus( 'SMW::SQLStore::updatePropertyTableDefinitions', true );
				return PropertyRegistry::getInstance()->registerAsFixedTables( $propertyTableDefinitions, $configuration );
			};

			/**
			 * Register properties
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['smwInitProperties'][] = function () use( $observableReporter ) {
				$observableReporter->reportStatus( 'smwInitProperties', true );
				return PropertyRegistry::getInstance()->registerPropertiesAndAliases();
			};

			/**
			 * Execute and update annotations
			 *
			 * @since 1.0
			 */
			$globalVars['wgHooks']['SMWStore::updateDataBefore'][] = function ( \SMW\Store $store, \SMW\SemanticData $semanticData ) use ( $configuration, $observableReporter ) {
				$observableReporter->reportStatus( 'SMWStore::updateDataBefore', true );

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


			return true;
		};
	}

}
