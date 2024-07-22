<?php

namespace SESP;

use Hooks;
use MediaWiki\MediaWikiServices;
use SMW\Services\ServicesFactory;

/**
 * @license GPL-2.0-or-later
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
	 * @param array $config
	 */
	public function __construct( $config ) {
		$this->registerCallbackHandlers( $config );
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

			$hookContainer = MediaWikiServices::getInstance()->getHookContainer();
			$hookContainer->clear( $name );

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
	 * @since 2.0
	 *
	 * @param array &$vars
	 */
	public static function initExtension( &$vars ) {
		$vars['wgHooks']['SMW::Config::BeforeCompletion'][] = static function ( &$config ) {
			$exemptionlist = [
				'___EUSER', '___CUSER', '___SUBP', '___REVID', '___VIEWS',
				'___NREV', '___NTREV', '___USEREDITCNT', '___USEREDITCNTNS', '___EXIFDATA', '___NSID', '___NSNAME'
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

			// #93
			if ( isset( $config['smwgQueryDependencyPropertyExemptionList'] ) ) {
				$config['smwgQueryDependencyPropertyExemptionList'] = array_merge(
					$config['smwgQueryDependencyPropertyExemptionList'],
					$exemptionlist
				);
			}

			if ( isset( $config['smwgImportFileDirs'] ) ) {
				$config['smwgImportFileDirs'] += [ 'sesp' => __DIR__ . '/../data/import' ];
			}

			return true;
		};
	}

	private function registerCallbackHandlers( $config ) {
		$servicesFactory = ServicesFactory::getInstance();

		$appFactory = new AppFactory(
			$config,
			$servicesFactory->getCache()
		);

		$appFactory->setLogger(
			$servicesFactory->getMediaWikiLogger( 'sesp' )
		);

		$propertyRegistry = new PropertyRegistry(
			$appFactory
		);

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
		 */
		$this->handlers['SMW::Property::initProperties'] = static function ( $registry ) use ( $propertyRegistry ) {
			$propertyRegistry->register(
				$registry
			);

			return true;
		};

		/**
		 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::AddCustomFixedPropertyTables
		 */
		$this->handlers['SMW::SQLStore::AddCustomFixedPropertyTables'] = static function ( array &$customFixedProperties, &$fixedPropertyTablePrefix ) use( $propertyRegistry ) {
			$propertyRegistry->registerFixedProperties(
				$customFixedProperties,
				$fixedPropertyTablePrefix
			);

			return true;
		};

		/**
		 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/blob/master/docs/technical/hooks/hook.store.beforedataupdatecomplete.md
		 */
		$this->handlers['SMW::Store::BeforeDataUpdateComplete'] = static function ( $store, $semanticData ) use ( $appFactory ) {
			$extraPropertyAnnotator = new ExtraPropertyAnnotator(
				$appFactory
			);

			$extraPropertyAnnotator->addAnnotation( $semanticData );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Extension:Approved_Revs/Hooks/ApprovedRevsRevisionApproved
		 */
		$this->handlers['ApprovedRevsRevisionApproved'] = static function (
			$output, $title, $rev_id, $content
		) use (
			$servicesFactory
		) {
			// 1hr
			$ttl = 60 * 60; 

			// Send an event to ParserAfterTidy and allow it to pass the preliminary
			// test even in cases where the content doesn't contain any SMW related
			// annotations. It is to ensure that when an agent switches to a blank
			// version (no SMW related annotations or categories) the update is carried
			// out and the store is able to remove any remaining annotations.
			$key = smwfCacheKey( 'smw:parseraftertidy', $title->getPrefixedDBKey() );
			$servicesFactory->getCache()->save( $key, $rev_id, $ttl );

			return true;
		};

		/**
		 * https://www.mediawiki.org/wiki/Extension:Approved_Revs/Hooks/ApprovedRevsRevisionUnapproved
		 */
		$this->handlers['ApprovedRevsRevisionUnapproved'] = static function (
			$output, $title, $content
		) use (
			$servicesFactory
		) {
			// 1hr
			$ttl = 60 * 60; 
			$key = smwfCacheKey( 'smw:parseraftertidy', $title->getPrefixedDBKey() );
			$servicesFactory->getCache()->save( $key, null, $ttl );

			return true;
		};
	}

}
