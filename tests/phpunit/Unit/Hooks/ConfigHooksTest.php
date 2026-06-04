<?php

namespace SESP\Tests\Hooks;

use SESP\Hooks\ConfigHooks;

/**
 * @covers \SESP\Hooks\ConfigHooks
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class ConfigHooksTest extends \PHPUnit\Framework\TestCase {

	public function testExemptsVolatilePropertiesFromQueryDependencyDetection() {
		$configuration = [
			'smwgQueryDependencyPropertyExemptionList' => [ '_MDAT' ],
		];

		( new ConfigHooks() )->onSMW__Settings__BeforeInitializationComplete( $configuration );

		$this->assertSame(
			[
				'_MDAT',
				'___REVID',
				'___VIEWS',
				'___NREV',
				'___NTREV',
				'___USEREDITCNT',
				'___USEREDITCNTNS',
			],
			$configuration['smwgQueryDependencyPropertyExemptionList']
		);
	}

	public function testDoesNothingWhenExemptionListIsAbsent() {
		$configuration = [];

		( new ConfigHooks() )->onSMW__Settings__BeforeInitializationComplete( $configuration );

		$this->assertSame( [], $configuration );
	}
}
