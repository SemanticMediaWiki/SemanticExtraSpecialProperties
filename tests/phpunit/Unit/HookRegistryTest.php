<?php

namespace SESP\Tests;

use SESP\HookRegistry;

/**
 * @covers \SESP\HookRegistry
 *
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

		$this->doTestRegisteredInitPropertiesHandler( $instance );
		$this->doTestRegisteredUpdatePropertyTableDefinitionsHandler( $instance );
		$this->doTestRegisteredUpdateDataBeforeHandler( $instance );
	}

	public function testOnBeforeConfigCompletion() {

		$config = array(
			'smwgFulltextSearchPropertyExemptionList' => array()
		);

		$propertyExemptionList = array(
			'___EUSER',
			'___CUSER',
			'___SUBP',
			'___REVID',
			'___VIEWS',
			'___NREV',
			'___NTREV',
			'___USEREDITCNT',
			'___EXIFDATA'
		);

		HookRegistry::onBeforeConfigCompletion( $config );

		$this->assertEquals(
			array(
				'smwgFulltextSearchPropertyExemptionList' => $propertyExemptionList,
			),
			$config
		);
	}

	public function doTestRegisteredInitPropertiesHandler( $instance ) {

		$this->assertTrue(
			$instance->isRegistered( 'smwInitProperties' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'smwInitProperties' ),
			array()
		);
	}

	public function doTestRegisteredUpdatePropertyTableDefinitionsHandler( $instance ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMW::SQLStore::updatePropertyTableDefinitions' )
		);

		$propertyTableDefinitions = array();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::SQLStore::updatePropertyTableDefinitions' ),
			array( &$propertyTableDefinitions )
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
