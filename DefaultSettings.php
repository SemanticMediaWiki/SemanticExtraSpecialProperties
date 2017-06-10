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

return [

	/**
	 * To setup properties as fixed properties in order to improve data access
	 */
	'sespUseAsFixedTables' => false,

	/**
	 * Location of the property definitions
	 */
	'sespPropertyDefinitionFile' => __DIR__ . '/definitions.json',

	/**
	 * Specifies local definitions
	 */
	'sespLocalPropertyDefinitions' => [],

	/**
	 * Specifies the enabled properties
	 */
	'sespSpecialProperties' => [],

	/**
	 * Specifies an internal cache modifier
	 */
	'sespLabelCacheVersion' => '2017.06',

	/**
	 * It causes bot edits via user accounts in usergroup "bot" to be ignored when
	 * storing data for the special properties.
	 */
	'wgSESPExcludeBots' => false,

	/**
	 * Used in connection with ShortUrlUtils
	 */
	'wgShortUrlPrefix' => '',

];
