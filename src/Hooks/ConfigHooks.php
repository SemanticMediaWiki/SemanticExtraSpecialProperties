<?php

namespace SESP\Hooks;

/**
 * Contributes Semantic MediaWiki configuration defaults during settings
 * initialization.
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class ConfigHooks {

	/**
	 * Volatile, machine-maintained special properties whose values change as a
	 * byproduct of normal wiki activity (edits, view counts) rather than from a
	 * deliberate semantic change. Tracking query dependencies on them would
	 * cause needless query-result invalidation, so they are exempted — mirroring
	 * how SMW exempts its own byproduct properties such as `_MDAT`.
	 */
	private const QUERY_DEPENDENCY_EXEMPTION_LIST = [
		'___REVID',
		'___VIEWS',
		'___NREV',
		'___NTREV',
		'___USEREDITCNT',
		'___USEREDITCNTNS',
	];

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Settings::BeforeInitializationComplete
	 * @since 7.0.0
	 *
	 * @param array &$configuration
	 */
	public function onSMW__Settings__BeforeInitializationComplete( array &$configuration ): void {
		if ( !isset( $configuration['smwgQueryDependencyPropertyExemptionList'] ) ) {
			return;
		}

		$configuration['smwgQueryDependencyPropertyExemptionList'] = array_merge(
			$configuration['smwgQueryDependencyPropertyExemptionList'],
			self::QUERY_DEPENDENCY_EXEMPTION_LIST
		);
	}
}
