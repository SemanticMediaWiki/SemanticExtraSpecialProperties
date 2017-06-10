<?php

namespace SESP\Tests;

use SESP\PropertyRegistry;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyRegistry
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyRegistryTest extends \PHPUnit_Framework_TestCase {

	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PropertyRegistry::class,
			new PropertyRegistry( $this->appFactory )
		);
	}

	public function testRegisterOnEmptyDefinition() {

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			array()
		);

		$this->appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry(
			$this->appFactory
		);

		$instance->registerOn( $propertyRegistry );
	}

	public function testRegisterOnEmptyDefinitionOnExifDefintion() {

		$defs = [ '_EXIF' => [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt', 'label' => 'Foo' ] ]
		];

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$this->appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$propertyRegistry->expects( $this->once() )
			->method( 'registerProperty' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyAlias' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyAliasByMsgKey' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyDescriptionMsgKeyById' );

		$instance = new PropertyRegistry(
			$this->appFactory
		);

		$instance->registerOn( $propertyRegistry );
	}

	public function testRegisterOnFakeDefinition() {

		$definition['_MY_CUSTOM1'] = [
			'id'    => '___MY_CUSTOM1',
			'type'  => '_wpg',
			'alias' => 'some-...',
			'label' => 'SomeCustomProperty',
		];

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			$definition
		);

		$this->appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$propertyRegistry->expects( $this->once() )
			->method( 'registerProperty' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyAlias' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyAliasByMsgKey' );

		$propertyRegistry->expects( $this->once() )
			->method( 'registerPropertyDescriptionMsgKeyById' );

		$instance = new PropertyRegistry(
			$this->appFactory
		);

		$instance->registerOn( $propertyRegistry );
	}

	public function testRegisterAsFixedPropertiesDisabled() {

		$this->appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( $this->stringContains( 'sespUseAsFixedTables' ) )
			->will( $this->returnValue( false ) );

		$this->appFactory->expects( $this->never() )
			->method( 'getPropertyDefinitions' );

		$propertyRegistry = $this->getMockBuilder( '\SMW\PropertyRegistry' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new PropertyRegistry(
			$this->appFactory
		);

		$customFixedProperties = array();
		$fixedPropertyTablePrefix = array();

		$instance->registerAsFixedProperties( $customFixedProperties, $fixedPropertyTablePrefix );
	}

	public function testRegisterAsFixedPropertiesEnabled() {

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			array()
		);

		$this->appFactory->expects( $this->at( 0 ) )
			->method( 'getOption' )
			->with( $this->stringContains( 'sespUseAsFixedTables' ) )
			->will( $this->returnValue( true ) );

		$this->appFactory->expects( $this->at( 1 ) )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$this->appFactory->expects( $this->at( 2 ) )
			->method( 'getOption' )
			->with( $this->stringContains( 'sespSpecialProperties' ) )
			->will( $this->returnValue( array() ) );

		$instance = new PropertyRegistry(
			$this->appFactory
		);

		$customFixedProperties = array();
		$fixedPropertyTablePrefix = array();

		$instance->registerAsFixedProperties( $customFixedProperties, $fixedPropertyTablePrefix );
	}

}
