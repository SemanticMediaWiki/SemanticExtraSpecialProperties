<?php

namespace SESP;

/**
 * @codeCoverageIgnore
 */
class Hook {

	/**
	 * @since 1.4
	 */
	public static function callback( array $credits ): void {
		define( 'SESP_VERSION', $credits['version'] );

		// Cover legacy settings
		$deprecationNotices = [];

		if ( isset( $GLOBALS['sespUseAsFixedTables'] ) ) {
			$GLOBALS['sespgUseFixedTables'] = $GLOBALS['sespUseAsFixedTables'];
			$deprecationNotices['replacement']['sespUseAsFixedTables'] = 'sespgUseFixedTables';
		}

		if ( isset( $GLOBALS['sespPropertyDefinitionFile'] ) ) {
			$GLOBALS['sespgDefinitionsFile'] = $GLOBALS['sespPropertyDefinitionFile'];
			$deprecationNotices['replacement']['sespPropertyDefinitionFile'] = 'sespgDefinitionsFile';
		}

		if ( isset( $GLOBALS['sespLocalPropertyDefinitions'] ) ) {
			$GLOBALS['sespgLocalDefinitions'] = $GLOBALS['sespLocalPropertyDefinitions'];
			$deprecationNotices['replacement']['sespLocalPropertyDefinitions'] = 'sespgLocalDefinitions';
		}

		if ( isset( $GLOBALS['sespSpecialProperties'] ) ) {
			$GLOBALS['sespgEnabledPropertyList'] = $GLOBALS['sespSpecialProperties'];
			$deprecationNotices['replacement']['sespSpecialProperties'] = 'sespgEnabledPropertyList';
		}

		if ( isset( $GLOBALS['sespLabelCacheVersion'] ) ) {
			$GLOBALS['sespgLabelCacheVersion'] = $GLOBALS['sespLabelCacheVersion'];
			$deprecationNotices['replacement']['sespLabelCacheVersion'] = 'sespgLabelCacheVersion';
		}

		if ( isset( $GLOBALS['wgSESPExcludeBots'] ) ) {
			$GLOBALS['sespgExcludeBotEdits'] = $GLOBALS['wgSESPExcludeBots'];
			$deprecationNotices['replacement']['wgSESPExcludeBots'] = 'sespgExcludeBotEdits';
		}

		// Allow deprecated settings to appear on the `Special:SemanticMediaWiki`
		// "Deprecation notices" section
		if ( $deprecationNotices !== [] && !isset( $GLOBALS['smwgDeprecationNotices']['sesp'] ) ) {
			$GLOBALS['smwgDeprecationNotices']['sesp'] = [
				'replacement' => $deprecationNotices['replacement']
			];
		}
	}

	public static function onSetupAfterCache() {
		$config = [
			'sespgUseFixedTables'      => $GLOBALS['sespgUseFixedTables'],
			'sespgEnabledPropertyList' => $GLOBALS['sespgEnabledPropertyList'],
			'sespgExcludeBotEdits'     => $GLOBALS['sespgExcludeBotEdits'],
			'sespgDefinitionsFile'     => $GLOBALS['sespgDefinitionsFile'],
			'sespgLocalDefinitions'    => $GLOBALS['sespgLocalDefinitions'],
			'sespgLabelCacheVersion'   => $GLOBALS['sespgLabelCacheVersion'],

			// Non-SESP settings
			'wgDisableCounters'        => $GLOBALS['wgDisableCounters'] ?? null,
			'wgShortUrlPrefix'         => $GLOBALS['wgShortUrlPrefix'] ?? null,
		];

		$hookRegistry = new HookRegistry(
			$config
		);

		$hookRegistry->register();
	}
}
