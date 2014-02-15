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
	public function testGetPropertyIdWithUnknownIdentifier() {
		$this->assertNull( PropertyRegistry::getInstance()->getPropertyId( 'Foo' ) );
	}

	public function testRegisterPropertiesAndAliases() {
		PropertyRegistry::clear();
		$this->assertTrue( PropertyRegistry::getInstance()->registerPropertiesAndAliases() );
	}

	public function testRegisterNotAsFixedTables() {

		PropertyRegistry::clear();

		$tableDefinitions = array();
		$configuration = array();

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertEmpty( $tableDefinitions );
	}

	public function testRegisterAsFixedTablesSetFalse() {

		PropertyRegistry::clear();

		$tableDefinitions = array();
		$configuration = array(
			'sespUseAsFixedTables'  => false
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertCount( 0, $tableDefinitions );
	}

	public function testRegisterAsFixedTablesSetTrue() {

		PropertyRegistry::clear();

		$tableDefinitions = array();
		$configuration = array(
			'sespUseAsFixedTables'  => true,
			'sespSpecialProperties' => array( '_REVID' )
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertCount( 1, $tableDefinitions );
	}

	public function testRegisterAsFixedTablesWithAll() {

		PropertyRegistry::clear();

		$tableDefinitions = array();
		$configuration = array(
			'sespUseAsFixedTables'  => true,
			'sespSpecialProperties' => array(
				'_CUSER',
				'_EUSER',
				'_REVID',
				'_PAGEID',
				'_VIEWS',
				'_NREV',
				'_NTREV',
				'_SUBP',
				'_USERREG',
				'_EXIFSOFTWARE',
				'_EXIFDATETIME',
				'_MEDIATYPE',
				'_MIMETYPE',
				'_SHORTURL'
			)
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertCount( 14, $tableDefinitions );
	}

	public function testRegisterAsFixedTablesSetTrueWithInvalidPropertyId() {

		PropertyRegistry::clear();

		$tableDefinitions = array();
		$configuration = array(
			'sespUseAsFixedTables'  => true,
			'sespSpecialProperties' => array( '_FOO' )
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertCount( 0, $tableDefinitions );
	}

}
