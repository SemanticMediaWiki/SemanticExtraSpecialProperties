<?php
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

/* Set up extension */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'Not an entry point.' );
}

if ( version_compare( $wgVersion, '1.19', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires MediaWiki 1.19 or above.' );
}

if ( ! defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires <a href="http://semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> installed.<br />' );
}

if ( version_compare( SMW_VERSION, '1.7', '<' ) ) {
	die( '<b>Error:</b> This version of Semantic Extra Special Properties requires Semantic MediaWiki 1.7 or above.' );
}

define( 'SESP_VERSION', '0.2.7' );

$wgExtensionCredits['semantic'][] = array(
	'path'           => __FILE__,
	'name'           => 'Semantic Extra Special Properties',
	'author'         => array( 'Leo Wallentin', '[http://xn--ssongsmat-v2a.nu Säsongsmat.nu]', 'mwjames' ),
	'version'        => SESP_VERSION,
	'url'            => 'https://www.mediawiki.org/wiki/Extension:SemanticExtraSpecialProperties',
	'descriptionmsg' => 'sesp-desc',
);

$dir = dirname( __FILE__ ) . '/';

/**
 * Message class  
 */ 
$wgExtensionMessagesFiles['SemanticESP' ] = $dir . 'SemanticExtraSpecialProperties.i18n.php';

$wgAutoloadClasses[ 'SemanticESP'       ] = $dir . 'SemanticExtraSpecialProperties.hooks.php';

/* Hook into SMW */
$wgHooks['smwInitProperties'         ][] = 'SemanticESP::sespInitProperties';
$wgHooks['SMWStore::updateDataBefore'][] = 'SemanticESP::sespUpdateDataBefore';

