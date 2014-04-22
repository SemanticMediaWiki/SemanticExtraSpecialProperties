<?php

if ( php_sapi_name() !== 'cli' ) {
	die( 'Not an entry point' );
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'MediaWiki is not available for the test environment' );
}

function registerAutoloaderPath( $name, $path ) {
	print( "\nUsing the {$name} vendor autoloader ...\n\n" );
	return require $path;
}

function runTestAutoLoader( $autoLoader = null ) {

	$mwVendorPath = __DIR__ . '/../../../vendor/autoload.php';
	$localVendorPath = __DIR__ . '/../vendor/autoload.php';

	if ( is_readable( $localVendorPath ) ) {
		$autoLoader = registerAutoloaderPath( 'local', $localVendorPath );
	} elseif ( is_readable( $mwVendorPath ) ) {
		$autoLoader = registerAutoloaderPath( 'MediaWiki', $mwVendorPath );
	}

	if ( $autoLoader instanceof \Composer\Autoload\ClassLoader ) {
		return true;
	}

	return false;
}

if ( !runTestAutoLoader() ) {
	die( 'The required test autoloader was not accessible' );
}