<?php

$cfg = require __DIR__ . '/../vendor/mediawiki/mediawiki-phan-config/src/config.php';

$cfg['baseline_path'] = __DIR__ . '/baseline.php';

// Analyse the extension source code; vendor and node_modules are excluded by default.
$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'src',
	]
);

$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'vendor/',
	]
);

// Make the extensions SESP integrates with visible to Phan's type checker: those installed by the
// CI matrix. mediawiki-phan-config only adds MediaWiki core and its vendor directory to
// directory_list, never extensions/. Without this, calls into these extensions surface as
// PhanUndeclaredClass* noise instead of being checked against their actual API.
$IP = getenv( 'MW_INSTALL_PATH' ) !== false
	? str_replace( '\\', '/', getenv( 'MW_INSTALL_PATH' ) )
	: '../..';

$dependencyExtensions = [
	'SemanticMediaWiki',
	'ApprovedRevs',
	'PageImages',
];

foreach ( $dependencyExtensions as $ext ) {
	$path = $IP . '/extensions/' . $ext;

	if ( !is_dir( $path ) ) {
		continue;
	}

	$cfg['directory_list'][] = $path;
	$cfg['exclude_analysis_directory_list'][] = $path;
}

return $cfg;
