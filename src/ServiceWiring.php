<?php

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use SESP\AppFactory;
use SESP\ExtraPropertyAnnotator;
use SESP\PropertyRegistry;
use SMW\Services\ServicesFactory;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @license GPL-2.0-or-later
 */
return [

	'SESP.AppFactory' => static function ( MediaWikiServices $services ): AppFactory {
		$config = $services->getConfigFactory()->makeConfig( 'sespg' );

		$options = [
			'sespgUseFixedTables'      => $config->get( 'UseFixedTables' ),
			'sespgEnabledPropertyList' => $config->get( 'EnabledPropertyList' ),
			'sespgExcludeBotEdits'     => $config->get( 'ExcludeBotEdits' ),
			'sespgDefinitionsFile'     => $config->get( 'DefinitionsFile' ),
			'sespgLocalDefinitions'    => $config->get( 'LocalDefinitions' ),
			'sespgLabelCacheVersion'   => $config->get( 'LabelCacheVersion' ),

			// Provided by MediaWiki/other extensions rather than the `sespg`
			// config registry, so read from the globals directly.
			'wgDisableCounters'        => $GLOBALS['wgDisableCounters'] ?? null,
			'wgShortUrlPrefix'         => $GLOBALS['wgShortUrlPrefix'] ?? null,
		];

		$appFactory = new AppFactory(
			$options,
			ServicesFactory::getInstance()->getObjectCache()
		);

		$appFactory->setLogger( LoggerFactory::getInstance( 'sesp' ) );

		return $appFactory;
	},

	'SESP.PropertyRegistry' => static function ( MediaWikiServices $services ): PropertyRegistry {
		return new PropertyRegistry( $services->getService( 'SESP.AppFactory' ) );
	},

	'SESP.ExtraPropertyAnnotator' => static function ( MediaWikiServices $services ): ExtraPropertyAnnotator {
		return new ExtraPropertyAnnotator( $services->getService( 'SESP.AppFactory' ) );
	},

	'SESP.ObjectCache' => static function ( MediaWikiServices $services ): BagOStuff {
		return ServicesFactory::getInstance()->getObjectCache();
	},

];
