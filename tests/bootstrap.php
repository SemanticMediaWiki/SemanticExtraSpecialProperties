<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The Semantic MediaWiki test autoloader is not available' );
}

print sprintf( "\n%-20s%s\n", "Semantic Extra Special Properties: ", SESP_VERSION );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SESP\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SESP\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
