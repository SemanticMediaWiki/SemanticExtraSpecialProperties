<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings to configuration paramters in the "LocalSettings" file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the Semantic Extra Special Properties extension. It is not a valid entry point.' );
}

return [

	/**
	 * To setup properties as fixed properties in order to improve data access
	 */
	'sespgUseFixedTables' => false,

	/**
	 * Location of the property definitions
	 */
	'sespgDefinitionsFile' => __DIR__ . '/definitions.json',

	/**
	 * Specifies local definitions
	 */
	'sespgLocalDefinitions' => [],

	/**
	 * Specifies the enabled properties
	 */
	'sespgEnabledPropertiesList' => [],

	/**
	 * Specifies an internal cache modifier
	 */
	'sespgLabelCacheVersion' => '2017.07',

	/**
	 * It causes bot edits via user accounts in usergroup "bot" to be ignored when
	 * storing data for the special properties.
	 */
	'sespgExcludeBotEdits' => false,

	/**
	 * Used in connection with ShortUrlUtils
	 */
	'wgShortUrlPrefix' => '',

];
