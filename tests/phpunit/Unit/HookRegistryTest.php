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

		$configuration =  array();

		$this->assertInstanceOf(
			'\SESP\HookRegistry',
			new HookRegistry( $configuration )
		);
	}

	public function testRegister() {

		$configuration = array(
			'sespSpecialProperties' => array(),
			'wgDisableCounters' => false,
			'sespUseAsFixedTables' => false,
			'wgSESPExcludeBots' => false,
			'wgShortUrlPrefix' => '',
			'sespCacheType'    => 'hash'
		);

		$instance = new HookRegistry( $configuration );
		$instance->deregister();
		$instance->register();

		$this->doTestRegisteredSMWPropertyInitProperties( $instance );
		$this->doTestRegisteredSMMSQLStoreAddCustomFixedPropertyTables( $instance );
		$this->doTestRegisteredUpdateDataBeforeHandler( $instance );
	}

	public function doTestRegisteredSMWPropertyInitProperties( $instance ) {

		$handler = 'SMW::Property::initProperties';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( $handler ),
			array()
		);
	}

	public function doTestRegisteredSMMSQLStoreAddCustomFixedPropertyTables( $instance ) {

		$handler = 'SMW::SQLStore::AddCustomFixedPropertyTables';

		$this->assertTrue(
			$instance->isRegistered( $handler )
		);

		$customFixedProperties = array();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( $handler ),
			array( &$customFixedProperties )
		);
	}

	public function doTestRegisteredUpdateDataBeforeHandler( $instance ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMWStore::updateDataBefore' )
		);

		$store = $this->getMockBuilder( '\SMW\Store' )
			->disableOriginalConstructor()
			->getMockForAbstractClass();

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMWStore::updateDataBefore' ),
			array( $store, $semanticData )
		);
	}

	private function assertThatHookIsExcutable( array $hooks, $arguments ) {
		foreach ( $hooks as $hook ) {

			$this->assertInternalType(
				'boolean',
				call_user_func_array( $hook, $arguments )
			);
		}
	}

}
