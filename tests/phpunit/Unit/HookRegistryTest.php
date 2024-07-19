<?php

namespace SESP\Tests;

use SESP\HookRegistry;

/**
 * @covers \SESP\HookRegistry
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 1.3
 *
 * @author mwjames
 */
class HookRegistryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {
		$config =  [];

		$this->assertInstanceOf(
			'\SESP\HookRegistry',
			new HookRegistry( $config )
		);
	}

	public function testRegister() {
		$config = [
			'sespgDefinitionsFile' => $GLOBALS['sespgDefinitionsFile'],
			'sespgLocalDefinitions' => [],
			'sespgEnabledPropertyList' => [],
			'wgDisableCounters' => false,
			'sespgUseFixedTables' => false,
			'sespgExcludeBotEdits' => false,
			'sespgShortUrlPrefix' => '',
			'sespCacheType'    => 'hash'
		];

		$instance = new HookRegistry( $config );
		$instance->deregister();
		$instance->register();

		$this->doTestRegisteredInitPropertiesHandler( $instance );
		$this->doTestRegisteredAddCustomFixedPropertyTables( $instance );
		$this->doTestRegisteredUpdateDataBeforeHandler( $instance );
	}

	public function testInitExtension() {
		$vars = [];

		HookRegistry::initExtension( $vars );

		// CanonicalNamespaces
		$callback = end( $vars['wgHooks']['SMW::Config::BeforeCompletion'] );

		$config = [
			'smwgFulltextSearchPropertyExemptionList' => []
		];

		$propertyExemptionList = [
			'___EUSER',
			'___CUSER',
			'___SUBP',
			'___REVID',
			'___VIEWS',
			'___NREV',
			'___NTREV',
			'___USEREDITCNT',
			'___USEREDITCNTNS',
			'___EXIFDATA',
			'___NSID',
			'___NSNAME'
		];

		$this->assertThatHookIsExcutable(
			$callback,
			[ &$config ]
		);

		$this->assertEquals(
			[
				'smwgFulltextSearchPropertyExemptionList' => $propertyExemptionList,
			],
			$config
		);
	}

	public function doTestRegisteredInitPropertiesHandler( $instance ) {
		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( 'SMW::Property::initProperties' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::Property::initProperties' ),
			[ $propertyRegistry ]
		);
	}

	public function doTestRegisteredAddCustomFixedPropertyTables( $instance ) {
		$this->assertTrue(
			$instance->isRegistered( 'SMW::SQLStore::AddCustomFixedPropertyTables' )
		);

		$customFixedProperties = [];
		$fixedPropertyTablePrefix = [];

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::SQLStore::AddCustomFixedPropertyTables' ),
			[ &$customFixedProperties, &$fixedPropertyTablePrefix ]
		);
	}

	public function doTestRegisteredUpdateDataBeforeHandler( $instance ) {
		$this->assertTrue(
			$instance->isRegistered( 'SMW::Store::BeforeDataUpdateComplete' )
		);

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::Store::BeforeDataUpdateComplete' ),
			[ $store, $semanticData ]
		);
	}

	private function assertThatHookIsExcutable( $hooks, $arguments ) {
		if ( is_callable( $hooks ) ) {
			$hooks = [ $hooks ];
		}

		foreach ( $hooks as $hook ) {

			$this->assertIsBool(
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
