<?php

namespace SESP\Tests;

use SESP\Definition\DefinitionReader;
use SESP\PropertyRegistry;
use SMW\DIProperty;

use ReflectionClass;

/**
 * @uses \SESP\PropertyRegistry
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistryTest extends \PHPUnit_Framework_TestCase {

	protected $sespCacheType = null;

	protected function setUp() {
		parent::setUp();

		$this->sespCacheType = $GLOBALS['sespCacheType'];
		$GLOBALS['sespCacheType'] = 'hash';
	}

	protected function tearDown() {
		$GLOBALS['sespCacheType'] = $this->sespCacheType;

		parent::tearDown();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SESP\PropertyRegistry',
			PropertyRegistry::getInstance()
		);
	}

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

	public function testGetPropertyIdWithUnknownIdentifier() {
		$this->assertNull( PropertyRegistry::getInstance()->getPropertyId( 'Foo' ) );
	}

	public function testGetPropertyType() {
		$this->assertInternalType(
			'integer',
			PropertyRegistry::getInstance()->getPropertyType( '_CUSER' )
		);
	}

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

		$definitionReader = new DefinitionReader;
		$definitions = $definitionReader->getDefinitions();

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

	public function testPropertyWithVisibility() {

		$propertydefinition = array(
			'_EXIF' => array(),
			'_FOOOOO'  => array(
				'id'     => '___FOOOOO',
				'type'   => 1,
				'show'   => true,
				'msgkey' => 'fooooo'
			)
		);

		$propertyId = $this->registerPropertyIdWithDefinition( '_FOOOOO', $propertydefinition );
		$property = new DIProperty( $propertyId );

		$this->assertInstanceOf( '\SMW\DIProperty', $property );
		$this->assertTrue( $property->isShown() );
	}

	public function testPropertyWithoutVisibility() {

		$propertydefinition = array(
			'_EXIF' => array(),
			'_FOOOOO'  => array(
				'id'     => '___FOOOOO',
				'type'   => 1,
				'show'   => false,
				'msgkey' => 'fooooo'
			)
		);

		$propertyId = $this->registerPropertyIdWithDefinition( '_FOOOOO', $propertydefinition );
		$property = new DIProperty( $propertyId );

		$this->assertInstanceOf( '\SMW\DIProperty', $property );
		$this->assertFalse( $property->isShown() );
	}

	public function testPropertyWithoutMsgKey() {

		$propertydefinition = array(
			'_EXIF' => array(),
			'_FOOOOO'  => array(
				'id'     => '___FOOOOO',
				'type'   => 1
			)
		);

		$propertyId = $this->registerPropertyIdWithDefinition( '_FOOOOO', $propertydefinition );
		$property = new DIProperty( $propertyId );

		$this->assertInstanceOf( '\SMW\DIProperty', $property );
	}

/* FIXME see SMW::core a7db2039c513751ab357c49b87041f2d167f0161
	public function testPropertyWithInvalidType() {

		$propertydefinition = array(
			'_EXIF' => array(),
			'_FOOOOO'  => array(
				'id'     => '___FOOOOO',
				'type'   => 9999
			)
		);

		$propertyId = $this->registerPropertyIdWithDefinition( '_FOOOOO', $propertydefinition );
		$property = new DIProperty( $propertyId );

		$this->assertInstanceOf( '\SMW\DIProperty', $property );
	}
*/

	public function testPropertyWithoutType() {

		$propertydefinition = array(
			'_EXIF' => array(),
			'_FOOOOO'  => array(
				'id'     => '___FOOOOO'
			)
		);

		$propertyId = $this->registerPropertyIdWithDefinition( '_FOOOOO', $propertydefinition );
		$property = new DIProperty( $propertyId );

		$this->assertInstanceOf( '\SMW\DIProperty', $property );
	}

	protected function registerPropertyIdWithDefinition( $id, $propertydefinition ) {

		$instance = PropertyRegistry::getInstance();

		$reflector = new ReflectionClass( '\SESP\PropertyRegistry' );
		$definitions = $reflector->getProperty( 'definitions' );
		$definitions->setAccessible( true );
		$definitions->setValue( $instance, $propertydefinition );

		$this->assertTrue( $instance->registerPropertiesAndAliases() );

		$propertyId = $instance->getPropertyId( $id );
		$instance->clear();

		return $propertyId;
	}

}
