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

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SESP\PropertyRegistry',
			PropertyRegistry::getInstance()
		);
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testJsonFileAvailability() {
		$this->assertTrue( is_file( $this->getJsonFile() ) );
	}

	/**
	 * @depends testJsonFileAvailability
	 */
	public function testInaccessibleJsonFile() {

		$this->setExpectedException( 'RuntimeException' );
		PropertyRegistry::getInstance()->acquireDefinitionsFromJsonFile( 'foo.json' );
	}

	/**
	 * @depends testJsonFileAvailability
	 */
	public function testMalformedJsonFile() {

		$this->setExpectedException( 'UnexpectedValueException' );
		PropertyRegistry::getInstance()->acquireDefinitionsFromJsonFile( __DIR__ . '/' . 'malformed.json' );
	}

	/**
	 * @depends testJsonFileAvailability
	 */
	public function testAcquireDefinitionsFromJsonFile() {
		$this->assertInternalType(
			'array',
			PropertyRegistry::getInstance()->acquireDefinitionsFromJsonFile( $this->getJsonFile() )
		);
	}

	/**
	 * @depends testAcquireDefinitionsFromJsonFile
	 */
	public function testGetPropertyId() {
		$this->assertInternalType(
			'string',
			PropertyRegistry::getInstance()->getPropertyId( '_CUSER' )
		);
	}

	public function testGetExifPropertyIdWithMixedcaseIdentifier() {
		$this->assertInternalType(
			'string',
			PropertyRegistry::getInstance()->getPropertyId( 'soFtWare' )
		);
	}

	/**
	 * @depends testGetPropertyId
	 */
	public function testGetPropertyIdWithUnknownIdentifier() {
		$this->assertNull( PropertyRegistry::getInstance()->getPropertyId( 'Foo' ) );
	}

	/**
	 * @depends testAcquireDefinitionsFromJsonFile
	 */
	public function testGetPropertyType() {
		$this->assertInternalType(
			'integer',
			PropertyRegistry::getInstance()->getPropertyType( '_CUSER' )
		);
	}

	/**
	 * @depends testGetPropertyType
	 */
	public function testGetPropertyTypeWithUnknownIdentifier() {
		$this->assertNull( PropertyRegistry::getInstance()->getPropertyType( 'Foo' ) );
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

	public function testRegisterAsFixedTablesWithNonExifProperties() {

		PropertyRegistry::clear();

		$definitions = PropertyRegistry::getInstance()->acquireDefinitionsFromJsonFile( $this->getJsonFile() );
		$this->assertTrue( isset( $definitions['_EXIF'] ) );

		unset( $definitions['_EXIF'] );
		$expectedCount = count( array_keys( $definitions ) );

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
				'_EXIFDATA',
				'_MEDIATYPE',
				'_MIMETYPE',
				'_SHORTURL'
			)
		);

		PropertyRegistry::getInstance()->registerAsFixedTables( $tableDefinitions, $configuration );
		$this->assertCount( $expectedCount, $tableDefinitions );
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

	protected function getJsonFile() {
		return PropertyRegistry::getInstance()->getJsonFile();
	}

}
