<?php


/**
 * Extension SemanticExtraSpecialProperties - Adds some extra special properties to all pages.
 * 
 * This extension was initially developed for http://xn--ssongsmat-v2a.nu Säsongsmat.nu.
 *
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/blob/master/README.md Documentation
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/blob/master/CHANGELOG.md Changelog
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues Support
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties Source code
 *
 * @author Leo Wallentin (Rotsee)
 * @author James Hong Kong (Mwjames)
 * @author Jeroen De Dauw
 * @author Karsten Hoffmeyer (Kghbln)
 * @license https://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// Prevent direct entry
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SESP_VERSION', '1.1-alpha' );

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

// Register extension
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
	'descriptionmsg' => 'sesp-desc'
);

// Tell file locations
$GLOBALS['wgExtensionMessagesFiles']['semantic-extra-special-properties'] = __DIR__ . '/SemanticExtraSpecialProperties.i18n.php';

$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ExtraPropertyAnnotator']   = __DIR__ . '/src/Annotator/ExtraPropertyAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\BaseAnnotator']            = __DIR__ . '/src/Annotator/BaseAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\PropertyRegistry']                  = __DIR__ . '/src/PropertyRegistry.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ExifDataAnnotator']       = __DIR__ . '/src/Annotator/ExifDataAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Annotator\ShortUrlAnnotator']       = __DIR__ . '/src/Annotator/ShortUrlAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\Setup']                             = __DIR__ . '/src/Setup.php';

\SESP\Setup::getInstance()->setGlobalVars( $GLOBALS )->run();

