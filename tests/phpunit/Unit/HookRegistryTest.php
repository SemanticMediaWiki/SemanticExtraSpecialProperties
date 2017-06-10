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
			'sespPropertyDefinitionFile' => $GLOBALS['sespPropertyDefinitionFile'],
			'sespLocalPropertyDefinitions' => array(),
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
		$this->doTestRegisteredAddCustomFixedPropertyTables( $instance );
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

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertTrue(
			$instance->isRegistered( 'SMW::Property::initProperties' )
		);

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::Property::initProperties' ),
			array( $propertyRegistry )
		);
	}

	public function doTestRegisteredAddCustomFixedPropertyTables( $instance ) {

		$this->assertTrue(
			$instance->isRegistered( 'SMW::SQLStore::AddCustomFixedPropertyTables' )
		);

		$customFixedProperties = array();
		$fixedPropertyTablePrefix = array();

		$this->assertThatHookIsExcutable(
			$instance->getHandlers( 'SMW::SQLStore::AddCustomFixedPropertyTables' ),
			array( &$customFixedProperties, &$fixedPropertyTablePrefix )
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
