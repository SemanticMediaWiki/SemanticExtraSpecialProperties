<?php

namespace SESP;

use SESP\Annotator\ExtraPropertyAnnotator;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class Setup {

	/** @var array */
	protected $globalVars;

	/**
	 * @since 1.0
	 *
	 * @return Extension
	 */
	public static function getInstance() {
		return new self();
	}

	/**
	 * @since 1.0
	 *
	 * @param array $globalVars
	 *
	 * @return Extension
	 */
	public function setGlobalVars( &$globalVars ) {
		$this->globalVars =& $globalVars;
		return $this;
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function run() {
		$this->deferredHookRegistration();
	}

	protected function deferredHookRegistration() {

		// FIXME PHP 5.3 doesn't allow to use $this reference within a closure
		// when 5.3 is obsolete use $this instead (PHP 5.4+)
		$globalVars = $this->globalVars;

		$this->globalVars['wgExtensionFunctions']['semantic-extra-special-properties'] = function( $reporter = null ) use( $globalVars) {

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
