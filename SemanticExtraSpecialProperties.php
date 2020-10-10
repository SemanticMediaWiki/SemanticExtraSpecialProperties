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
	die( 'This file is part of the SemanticExtraSpecialProperties extension, it is not a valid entry point.' );
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
	}

	/**
	 * @since 1.4
	 */
	public static function initExtension( $credits = [] ) {

		// See https://phabricator.wikimedia.org/T151136
		define( 'SESP_VERSION', isset( $credits['version'] ) ? $credits['version'] : 'UNKNOWN' );

		$GLOBALS['wgMessagesDirs']['SemanticExtraSpecialProperties'] = __DIR__ . '/i18n';

		// Register hooks that require to be listed as soon as possible and preferable
		// before the execution of onExtensionFunction
		HookRegistry::initExtension( $GLOBALS );
	}

	/**
	 * @since 1.4
	 */
	public static function onExtensionFunction() {

		if ( !defined( 'SMW_VERSION' ) ) {
			if ( PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ) {
				die( "\nThe 'Semantic Extra Special Properties' extension requires 'Semantic MediaWiki' to be installed and enabled.\n" );
			} else {
				die( '<b>Error:</b> The <a href="https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/">Semantic Extra Special Properties</a> extension requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> to be installed and enabled.<br />' );
			}
		}

		// Cover legacy settings
		$deprecationNotices = [];

		if ( isset( $GLOBALS['sespUseAsFixedTables'] ) ) {
			$GLOBALS['sespgUseFixedTables'] = $GLOBALS['sespUseAsFixedTables'];
			$deprecationNotices['replacement']['sespUseAsFixedTables'] = 'sespgUseFixedTables';
		}

		if ( isset( $GLOBALS['sespPropertyDefinitionFile'] ) ) {
			$GLOBALS['sespgDefinitionsFile'] = $GLOBALS['sespPropertyDefinitionFile'];
			$deprecationNotices['replacement']['sespPropertyDefinitionFile'] = 'sespgDefinitionsFile';
		}

		if ( isset( $GLOBALS['sespLocalPropertyDefinitions'] ) ) {
			$GLOBALS['sespgLocalDefinitions'] = $GLOBALS['sespLocalPropertyDefinitions'];
			$deprecationNotices['replacement']['sespLocalPropertyDefinitions'] = 'sespgLocalDefinitions';
		}

		if ( isset( $GLOBALS['sespSpecialProperties'] ) ) {
			$GLOBALS['sespgEnabledPropertyList'] = $GLOBALS['sespSpecialProperties'];
			$deprecationNotices['replacement']['sespSpecialProperties'] = 'sespgEnabledPropertyList';
		}

		if ( isset( $GLOBALS['sespLabelCacheVersion'] ) ) {
			$GLOBALS['sespgLabelCacheVersion'] = $GLOBALS['sespLabelCacheVersion'];
			$deprecationNotices['replacement']['sespLabelCacheVersion'] = 'sespgLabelCacheVersion';
		}

		if ( isset( $GLOBALS['wgSESPExcludeBots'] ) ) {
			$GLOBALS['sespgExcludeBotEdits'] = $GLOBALS['wgSESPExcludeBots'];
			$deprecationNotices['replacement']['wgSESPExcludeBots'] = 'sespgExcludeBotEdits';
		}

		// Allow deprecated settings to appear on the `Special:SemanticMediaWiki`
		// "Deprecation notices" section
		if ( $deprecationNotices !== [] && !isset( $GLOBALS['smwgDeprecationNotices']['sesp'] ) ) {
			$GLOBALS['smwgDeprecationNotices']['sesp'] = [ 'replacement' => $deprecationNotices['replacement'] ];
		}

		$config = [
			'sespgUseFixedTables'      => $GLOBALS['sespgUseFixedTables'],
			'sespgEnabledPropertyList' => $GLOBALS['sespgEnabledPropertyList'],
			'sespgExcludeBotEdits'     => $GLOBALS['sespgExcludeBotEdits'],
			'sespgDefinitionsFile'     => $GLOBALS['sespgDefinitionsFile'],
			'sespgLocalDefinitions'    => $GLOBALS['sespgLocalDefinitions'],
			'sespgLabelCacheVersion'   => $GLOBALS['sespgLabelCacheVersion'],

			// Non-SESP settings
			'wgDisableCounters'        => $GLOBALS['wgDisableCounters'] ?? null,
			'wgShortUrlPrefix'         => $GLOBALS['wgShortUrlPrefix'],
		];

		$hookRegistry = new HookRegistry(
			$config
		);

		$hookRegistry->register();
	}

	/**
	 * @since 1.4
	 *
	 * @return string|null
	 */
	public static function getVersion() {

		if ( !defined( 'SESP_VERSION' ) ) {
			return null;
		}

		return SESP_VERSION;
	}

}
