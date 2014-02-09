<?php

use SESP\PropertyRegistry;
use SESP\PredefinedPropertyAnnotator;

/**
 * Extension SemanticExtraSpecialProperties - Adds some extra special properties to all pages.
 *
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/blob/master/README.md Documentation
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/blob/master/CHANGELOG.md Changlog
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties/issues Support
 * @link https://github.com/SemanticMediaWiki/SemanticExtraSpecialProperties Source code
 *
 * @author Leo Wallentin (Rotsee)
 * @author James Hong Kong (Mwjames)
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

// Prevent direct entry
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( defined( 'SESP_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

define( 'SESP_VERSION', '0.3 alpha' );

if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	include_once( __DIR__ . '/vendor/autoload.php' );
}

if ( version_compare( $GLOBALS['wgVersion'], '1.19', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires MediaWiki 1.20 or above.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
}

if ( version_compare( SMW_VERSION, '1.7', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires Semantic MediaWiki 1.7 or above.' );
}

// Register extension
$GLOBALS['wgExtensionCredits']['semantic'][] = array(
	'path'           => __FILE__,
	'name'           => 'Semantic Extra Special Properties',
	'author'         => array(
		'[https://github.com/rotsee Leo Wallentin]',
		'[http://xn--ssongsmat-v2a.nu Säsongsmat.nu]',
		'[https://semantic-mediawiki.org/wiki/User:MWJames James Hong Kong]'
	),
	'version'        => SESP_VERSION,
	'url'            => 'https://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
	'descriptionmsg' => 'sesp-desc'
);

// Tell file locations
$GLOBALS['wgExtensionMessagesFiles']['SemanticESP'] = __DIR__ . '/SemanticExtraSpecialProperties.i18n.php';

$GLOBALS['wgAutoloadClasses']['SESP'] = __DIR__ . '/src/SESP.php';
$GLOBALS['wgAutoloadClasses']['SESP\PredefinedPropertyAnnotator'] = __DIR__ . '/src/PredefinedPropertyAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\PropertyRegistry']            = __DIR__ . '/src/PropertyRegistry.php';
$GLOBALS['wgAutoloadClasses']['SESP\ImageMetadataAnnotator']      = __DIR__ . '/src/ImageMetadataAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\ShortUrlAnnotator']           = __DIR__ . '/src/ShortUrlAnnotator.php';

PropertyRegistry::getInstance()->registerAsFixedTables( $GLOBALS );

/**
 * Setup and initialization
 *
 * @since 0.3
 */
$GLOBALS['wgExtensionFunctions'][] = function() {

	/**
	 * Collect configuration parameters
	 *
	 * @since 0.3
	 */
	$configuration = array(
		'wgDisableCounters'     => $GLOBALS['wgDisableCounters'],
		'sespSpecialProperties' => isset( $GLOBALS['sespSpecialProperties'] ) ? $GLOBALS['sespSpecialProperties'] : array(),
		'wgSESPExcludeBots'     => isset( $GLOBALS['wgSESPExcludeBots'] ) ? $GLOBALS['wgSESPExcludeBots'] : false,
		'wgShortUrlPrefix'      => isset( $GLOBALS['wgShortUrlPrefix'] )  ? $GLOBALS['wgShortUrlPrefix']  : false
	);

	/**
	 * Register properties
	 *
	 * @since 0.3
	 */
	$GLOBALS['wgHooks']['smwInitProperties'][] = function () {
		return PropertyRegistry::getInstance()->registerPropertiesAndAliases();
	};

	/**
	 * Execute and update annotation
	 *
	 * @since 0.3
	 */
	$GLOBALS['wgHooks']['SMWStore::updateDataBefore'][] = function ( \SMW\Store $store, \SMW\SemanticData $semanticData ) use ( $configuration ) {
		$propertyAnnotator = new PredefinedPropertyAnnotator( $semanticData, $configuration );

		// DI object registration
		$propertyAnnotator->registerObject( 'DBConnection', function() {
			return wfGetDB( DB_SLAVE );
		} );

		$propertyAnnotator->registerObject( 'WikiPage', function( $instance ) {
			return \WikiPage::factory( $instance->getSemanticData()->getSubject()->getTitle() );
		} );

		$propertyAnnotator->registerObject( 'UserByName', function( $instance ) {
			return \User::newFromName( $instance->getWikiPage()->getTitle()->getText() );
		} );

		return $propertyAnnotator->addAnnotation();
	};

};
