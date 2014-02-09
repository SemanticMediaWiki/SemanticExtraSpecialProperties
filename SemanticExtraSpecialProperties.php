<?php

use SESP\PropertyRegistry;
use SESP\PredefinedPropertyAnnotator;

/**
 * Extension SemanticExtraSpecialProperties - Adds some extra special properties to all pages.
 * @version 0.2.6 - 2012/10/05
 *
 * @link http://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties Documentation
 *
 * @file SemanticExtraSpecialProperties.php
 * @ingroup Extensions
 * @package MediaWiki
 * @author Leo Wallentin (Rotsee), mwjames,  nischayn22
 * @license http://www.opensource.org/licenses/BSD-2-Clause BSD
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $GLOBALS['wgVersion'], '1.19', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires MediaWiki 1.20 or above.' );
}

if ( ! defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
}

if ( version_compare( SMW_VERSION, '1.7', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires Semantic MediaWiki 1.7 or above.' );
}

define( 'SESP_VERSION', '0.3 alpha' );

$GLOBALS['wgExtensionCredits']['semantic'][] = array(
	'path'           => __FILE__,
	'name'           => 'Semantic Extra Special Properties',
	'author'         => array(
		'[https://github.com/rotsee Leo Wallentin]',
		'[http://xn--ssongsmat-v2a.nu Säsongsmat.nu]',
		'[https://semantic-mediawiki.org/wiki/User:MWJames mwjames]'
	),
	'version'        => SESP_VERSION,
	'url'            => 'http://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
	'descriptionmsg' => 'sesp-desc',
);

$GLOBALS['wgExtensionMessagesFiles']['SemanticESP'] = __DIR__ . '/SemanticExtraSpecialProperties.i18n.php';

$GLOBALS['wgAutoloadClasses']['SESP'] = __DIR__ . '/src/SESP.php';
$GLOBALS['wgAutoloadClasses']['SESP\PredefinedPropertyAnnotator'] = __DIR__ . '/src/PredefinedPropertyAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\PropertyRegistry']            = __DIR__ . '/src/PropertyRegistry.php';
$GLOBALS['wgAutoloadClasses']['SESP\ImageMetadataAnnotator']      = __DIR__ . '/src/ImageMetadataAnnotator.php';
$GLOBALS['wgAutoloadClasses']['SESP\ShortUrlAnnotator']           = __DIR__ . '/src/ShortUrlAnnotator.php';

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
		return PropertyRegistry::getInstance()->register();
	};

	/**
	 * Execute and update annotation
	 *
	 * @since 0.3
	 */
	$GLOBALS['wgHooks']['SMWStore::updateDataBefore'][] = function ( \SMW\Store $store, \SMW\SemanticData $semanticData ) use ( $configuration ) {
		$propertyAnnotator = new PredefinedPropertyAnnotator( $semanticData, $configuration );

		// DI object registration
		$propertyAnnotator->registerObject( 'DBConnection', function( $instance ) {
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
