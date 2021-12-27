<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

$autoloaderClassPath = ( getenv( "MW_INSTALL_PATH" ) ?: dirname( dirname( dirname( __DIR__ ) ) ) )
					 . "/extensions/SemanticMediaWiki/tests/autoloader.php";

if ( !is_readable( $autoloaderClassPath ) ) {
	die( "The Semantic MediaWiki test autoloader ($autoloaderClassPath) is not available" );
}

print sprintf( "\n%-20s%s\n", "Semantic Extra Special Properties: ", SESP_VERSION );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SESP\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SESP\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
