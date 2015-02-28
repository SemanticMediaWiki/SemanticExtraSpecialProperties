<?php

use SESP\HookRegistry;

/**
 * Extension SemanticExtraSpecialProperties - Adds some extra special properties to all pages.
 *
 * @license GNU GPL v2+
 * @since 0.1
 *
 * @author Leo Wallentin (Rotsee)
 * @author James Hong Kong (Mwjames)
 * @author Jeroen De Dauw
 * @author Karsten Hoffmeyer (Kghbln)
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticMetaTags extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS['wgVersion'], '1.20', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires MediaWiki 1.20 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
}

if ( version_compare( SMW_VERSION, '1.9', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires Semantic MediaWiki 1.9 or above.' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SESP_VERSION', '1.3-alpha' );

/**
 * @codeCoverageIgnore
 */
call_user_func( function () {

	// Register extension info
	$GLOBALS['wgExtensionCredits']['semantic'][] = array(
		'path'           => __FILE__,
		'name'           => 'Semantic Extra Special Properties',
		'author'         => array(
			'[https://github.com/rotsee Leo Wallentin]',
			'[https://semantic-mediawiki.org/wiki/User:MWJames James Hong Kong]',
			'...'
		),
		'version'        => SESP_VERSION,
		'url'            => 'https://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
		'descriptionmsg' => 'sesp-desc',
		'license-name'   => 'GPL-2.0+'
	);

	// Default setting
	$GLOBALS['sespCacheType'] = CACHE_ANYTHING;
	$GLOBALS['sespUseAsFixedTables'] = false;
	$GLOBALS['sespSpecialProperties'] = array();
	$GLOBALS['wgSESPExcludeBots'] = false;
	$GLOBALS['wgShortUrlPrefix'] = '';

	$GLOBALS['wgMessagesDirs']['semantic-extra-special-properties'] = __DIR__ . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['semantic-extra-special-properties'] = __DIR__ . '/i18n/SemanticExtraSpecialProperties.i18n.php';

	// Finalize extension setup
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$configuration = array(
			'wgDisableCounters'     => $GLOBALS['wgDisableCounters'],
			'sespUseAsFixedTables'  => $GLOBALS['sespUseAsFixedTables'],
			'sespSpecialProperties' => $GLOBALS['sespSpecialProperties'],
			'wgSESPExcludeBots'     => $GLOBALS['wgSESPExcludeBots'],
			'wgShortUrlPrefix'      => $GLOBALS['wgShortUrlPrefix'],
			'sespCacheType'         => $GLOBALS['sespCacheType']
		);

		$hookRegistry = new HookRegistry( $configuration );
		$hookRegistry->register();
	};

} );
