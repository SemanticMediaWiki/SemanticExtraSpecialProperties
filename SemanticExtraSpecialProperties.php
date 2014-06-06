<?php

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

// Prevent direct entry
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SESP_VERSION', '1.2.0' );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
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

// FIXME Use the PSR-4 Composer autoloader
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ExtraPropertyAnnotator']   = __DIR__ . '/src/Annotator/ExtraPropertyAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\BaseAnnotator']            = __DIR__ . '/src/Annotator/BaseAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ExifDataAnnotator']        = __DIR__ . '/src/Annotator/ExifDataAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ShortUrlAnnotator']        = __DIR__ . '/src/Annotator/ShortUrlAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Definition\DefinitionReader'] = __DIR__ . '/src/Definition/DefinitionReader.php';
$GLOBALS['wgAutoloadClasses']['SESP\Cache\MessageCache']   = __DIR__ . '/src/Cache/MessageCache.php';
$GLOBALS['wgAutoloadClasses']['SESP\Setup']                = __DIR__ . '/src/Setup.php';
$GLOBALS['wgAutoloadClasses']['SESP\ObservableReporter']   = __DIR__ . '/src/ObservableReporter.php';
$GLOBALS['wgAutoloadClasses']['SESP\PropertyRegistry']     = __DIR__ . '/src/PropertyRegistry.php';

/**
 * @codeCoverageIgnore
 *
 * @since 1.2.0
 */
call_user_func( function () {

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

	$setup = new \SESP\Setup( $GLOBALS, __DIR__ );
	$setup->run();

} );
