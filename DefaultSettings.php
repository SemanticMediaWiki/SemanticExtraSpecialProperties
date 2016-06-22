<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings in the LocalSettings file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticExtraSpecialProperties extension, it is not a valid entry point.' );
}

/**
 * MessageCache
 */
$GLOBALS['sespCacheType'] = CACHE_ANYTHING;

/**
 * To setup properties as fixed properties in order to improve data access
 */
$GLOBALS['sespUseAsFixedTables'] = false;

/**
 * Specifies the enabled properties
 */
$GLOBALS['sespSpecialProperties'] = array();

/**
 * It causes bot edits via user accounts in usergroup "bot" to be ignored when
 * storing data for the special properties.
 */
$GLOBALS['wgSESPExcludeBots'] = false;

/**
 * Use in connection with ShortUrlUtils
 */
$GLOBALS['wgShortUrlPrefix'] = '';
