<?php

use SMW\MediaWiki\Hooks\HookRegistry;

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

if ( version_compare( $GLOBALS['wgVersion'], '1.25', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires MediaWiki 1.25 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticExtraSpecialProperties::initExtension();

$GLOBALS['wgExtensionFunctions'][] = function() {
	SemanticExtraSpecialProperties::onExtensionFunction();
};

/**
 * @codeCoverageIgnore
 */
class SemanticExtraSpecialProperties {

	/**
	 * @since 1.4
	 */
	public static function initExtension() {

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

		define( 'SESP_VERSION', '1.4.0-alpha' );

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once( __DIR__ . '/vendor/autoload.php' );
		}

		// Register extension info
		$GLOBALS['wgExtensionCredits']['semantic'][] = array(
			'path'           => __FILE__,
			'name'           => 'Semantic Extra Special Properties',
			'author'         => array(
				'[https://github.com/rotsee Leo Wallentin]',
				'[https://www.semantic-mediawiki.org/wiki/User:MWJames James Hong Kong]',
				'...'
			),
			'version'        => SESP_VERSION,
			'url'            => 'https://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
			'descriptionmsg' => 'sesp-desc',
			'license-name'   => 'GPL-2.0+'
		);

		$GLOBALS['wgMessagesDirs']['SemanticExtraSpecialProperties'] = __DIR__ . '/i18n';
		$GLOBALS['wgExtensionMessagesFiles']['SemanticExtraSpecialProperties'] = __DIR__ . '/i18n/SemanticExtraSpecialProperties.i18n.php';
	}

	/**
	 * @since 1.4
	 */
	public static function onExtensionFunction() {

		$configuration = array(
			'wgDisableCounters'     => $GLOBALS['wgDisableCounters'],
			'sespUseAsFixedTables'  => $GLOBALS['sespUseAsFixedTables'],
			'sespSpecialProperties' => $GLOBALS['sespSpecialProperties'],
			'wgSESPExcludeBots'     => $GLOBALS['wgSESPExcludeBots'],
			'wgShortUrlPrefix'      => $GLOBALS['wgShortUrlPrefix'],
			'sespCacheType'         => $GLOBALS['sespCacheType']
		);

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
