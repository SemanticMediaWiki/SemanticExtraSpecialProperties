<?php

namespace SESP\Tests;

use SESP\PropertyRegistry;

/**
 * @covers \SESP\PropertyRegistry
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 */
class PropertyRegistryTest extends \PHPUnit_Framework_TestCase {

	public function getClass() {
		return '\SESP\PropertyRegistry';
	}

	public function testCanConstruct() {
		$this->assertInstanceOf( $this->getClass(), PropertyRegistry::getInstance() );
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testGetPropertyId() {
		$this->assertInternalType(
			'string',
			PropertyRegistry::getInstance()->getPropertyId( '_CUSER' )
		);
	}

	/**
	 * @depends testGetPropertyId
	 */
	public function testGetPropertyIdThrowsException() {

		$this->setExpectedException( 'InvalidArgumentException' );
		PropertyRegistry::getInstance()->getPropertyId( 'Foo' );

	}

	public function testRegisterPropertiesAndAliases() {

		PropertyRegistry::clear();
		$this->assertTrue( PropertyRegistry::getInstance()->registerPropertiesAndAliases() );

	}

	public function testRegisterNotAsFixedTables() {

		PropertyRegistry::clear();

		$notAsFixedTables = array();
		PropertyRegistry::getInstance()->registerAsFixedTables( $notAsFixedTables );
		$this->assertEmpty( $notAsFixedTables );

	}

	public function testRegisterAsFixedTablesSetFalse() {

		PropertyRegistry::clear();

		$asFixedTablesSetFalse = array(
			'sespUseAsFixedTables' => false,
			'smwgFixedProperties' => array()
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $asFixedTablesSetFalse );
		$this->assertCount( 0, $asFixedTablesSetFalse['smwgFixedProperties'] );

	}

	public function testRegisterAsFixedTablesSetTrue() {

		PropertyRegistry::clear();

		$asFixedTablesSetTrue = array(
			'sespUseAsFixedTables' => true,
			'smwgFixedProperties' => array()
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $asFixedTablesSetTrue );
		$this->assertCount( 13, $asFixedTablesSetTrue['smwgFixedProperties'] );

	}

}
