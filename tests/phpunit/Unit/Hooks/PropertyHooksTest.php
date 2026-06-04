<?php

namespace SESP\Tests\Hooks;

use SESP\ExtraPropertyAnnotator;
use SESP\Hooks\PropertyHooks;
use SESP\PropertyRegistry;
use SMW\DataModel\SemanticData;
use SMW\PropertyRegistry as Registry;
use SMW\Store;

/**
 * @covers \SESP\Hooks\PropertyHooks
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 5.0.0
 */
class PropertyHooksTest extends \PHPUnit\Framework\TestCase {

	private $propertyRegistry;
	private $extraPropertyAnnotator;

	protected function setUp(): void {
		parent::setUp();

		$this->propertyRegistry = $this->createMock( PropertyRegistry::class );
		$this->extraPropertyAnnotator = $this->createMock( ExtraPropertyAnnotator::class );
	}

	public function testInitPropertiesDelegatesToPropertyRegistry() {
		$registry = $this->createMock( Registry::class );

		$this->propertyRegistry->expects( $this->once() )
			->method( 'register' )
			->with( $registry )
			->willReturn( true );

		$instance = new PropertyHooks( $this->propertyRegistry, $this->extraPropertyAnnotator );

		$this->assertTrue(
			$instance->onSMW__Property__initProperties( $registry )
		);
	}

	public function testAddCustomFixedPropertyTablesDelegatesToPropertyRegistry() {
		$this->propertyRegistry->expects( $this->once() )
			->method( 'registerFixedProperties' );

		$instance = new PropertyHooks( $this->propertyRegistry, $this->extraPropertyAnnotator );

		$customFixedProperties = [];
		$fixedPropertyTablePrefix = [];

		$this->assertTrue(
			$instance->onSMW__SQLStore__AddCustomFixedPropertyTables(
				$customFixedProperties,
				$fixedPropertyTablePrefix
			)
		);
	}

	public function testBeforeDataUpdateCompleteDelegatesToAnnotator() {
		$semanticData = $this->createMock( SemanticData::class );
		$store = $this->createMock( Store::class );

		$this->extraPropertyAnnotator->expects( $this->once() )
			->method( 'addAnnotation' )
			->with( $semanticData );

		$instance = new PropertyHooks( $this->propertyRegistry, $this->extraPropertyAnnotator );

		$this->assertTrue(
			$instance->onSMW__Store__BeforeDataUpdateComplete( $store, $semanticData )
		);
	}
}
