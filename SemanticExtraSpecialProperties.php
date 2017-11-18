<?php

use SESP\HookRegistry;

/**
 * Extension SemanticExtraSpecialProperties - Adds some extra special properties to all pages.
 *
 * @see https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties
 *
 * @defgroup SemanticExtraSpecialProperties Semantic Extra Special Properties
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the Semantic Extra Special Properties extension. It is not a valid entry point.' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticExtraSpecialProperties::load();

/**
 * @codeCoverageIgnore
 */
class SemanticExtraSpecialProperties {

	/**
	 * @since 1.4
	 *
	 * @note It is expected that this function is loaded before LocalSettings.php
	 * to ensure that settings and global functions are available by the time
	 * the extension is activated.
	 */
	public static function load() {

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		foreach ( include __DIR__ . '/DefaultSettings.php' as $key => $value ) {
			if ( !isset( $GLOBALS[$key] ) ) {
				$GLOBALS[$key] = $value;
			}
		}

		/**
		 * In case extension.json is being used, the succeeding steps are
		 * expected to be handled by the ExtensionRegistry aka extension.json
		 * ...
		 *
		 * 	"callback": "SemanticExtraSpecialProperties::initExtension",
		 * 	"ExtensionFunctions": [
		 * 		"SemanticExtraSpecialProperties::onExtensionFunction"
		 * 	],
		 */
		self::initExtension();

		$GLOBALS['wgExtensionFunctions'][] = function() {
			self::onExtensionFunction();
		};
	}

	/**
	 * @since 1.4
	 */
	public static function initExtension() {

		define( 'SESP_VERSION', '2.0.0-alpha' );

		// Register extension info
		$GLOBALS['wgExtensionCredits']['semantic'][] = [
			'path'           => __FILE__,
			'name'           => 'Semantic Extra Special Properties',
			'author'         => [
				'[https://www.semantic-mediawiki.org/wiki/User:MWJames James Hong Kong]',
				'...'
			],
			'version'        => SESP_VERSION,
			'url'            => 'https://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
			'descriptionmsg' => 'sesp-desc',
			'license-name'   => 'GPL-2.0+'
		];

		$GLOBALS['wgMessagesDirs']['SemanticExtraSpecialProperties'] = __DIR__ . '/i18n';

		self::onBeforeExtensionFunction();
	}

	/**
	 * Register hooks that require to be listed as soon as possible and preferable
	 * before the execution of onExtensionFunction
	 *
	 * @since 1.4
	 */
	public static function onBeforeExtensionFunction() {
		$GLOBALS['wgHooks']['SMW::Config::BeforeCompletion'][] = '\SESP\HookRegistry::onBeforeConfigCompletion';
	}

	/**
	 * @since 2.0
	 */
	public static function checkRequirements() {

		if ( version_compare( $GLOBALS['wgVersion'], '1.27', '<' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/">Semantic Extra Special Properties</a> requires MediaWiki 1.27 or above.' );
		}

		if ( !defined( 'SMW_VERSION' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/">Semantic Extra Special Properties</a> requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
		}
	}

	/**
	 * @since 1.4
	 */
	public static function onExtensionFunction() {

		// Check requirements after LocalSetting.php has been processed
		self::checkRequirements();

		$configuration = [
			'sespUseAsFixedTables' => $GLOBALS['sespgUseFixedTables'],
			'sespSpecialProperties' => $GLOBALS['sespgEnabledPropertiesList'],
			'wgSESPExcludeBots' => $GLOBALS['sespgExcludeBotEdits'],
			'sespPropertyDefinitionFile' => $GLOBALS['sespgDefinitionsFile'],
			'sespLocalPropertyDefinitions' => $GLOBALS['sespgLocalDefinitions'],
			'sespLabelCacheVersion' => $GLOBALS['sespgLabelCacheVersion'],
			'sespPropertyDefinitions' => [],
			'wgDisableCounters' => $GLOBALS['wgDisableCounters'],
			'wgShortUrlPrefix' => $GLOBALS['wgShortUrlPrefix']
		];

		$hookRegistry = new HookRegistry(
			$configuration
		);

		$hookRegistry->register();
	}

	/**
	 * @since 1.4
	 *
	 * @return string|null
	 */
	public static function getVersion() {
		return SESP_VERSION;
	}

}
