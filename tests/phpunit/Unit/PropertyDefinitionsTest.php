<?php

namespace SESP\Tests;

use SESP\PropertyDefinitions;

/**
 * @covers \SESP\PropertyDefinitions
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyDefinitionsTest extends \PHPUnit_Framework_TestCase {

	private $labelFetcher;

	protected function setup() {
		parent::setUp();

		$this->labelFetcher = $this->getMockBuilder( '\SESP\LabelFetcher' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PropertyDefinitions::class,
			new PropertyDefinitions( $this->labelFetcher )
		);
	}

	public function testEmptyFile() {

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$this->assertInstanceOf(
			'\ArrayIterator',
			$instance->getIterator()
		);
	}

	public function testHasGet() {

		$defs = [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ]
		];

		$key = 'SOFTWARE';
		$expected = [ 'id' => 'Foo', 'type' => '_txt' ];

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->setPropertyDefinitions(
			$defs
		);

		$this->assertTrue(
			$instance->has( $key )
		);

		$this->assertEquals(
			$expected,
			$instance->get( $key )
		);
	}

	public function testSafeGet() {

		$defs = [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ]
		];

		$key = 'SOFTWARE';
		$expected = [ 'id' => 'Foo', 'type' => '_txt' ];

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->setPropertyDefinitions(
			$defs
		);

		$this->assertEquals(
			[],
			$instance->safeGet( 'Foo', [] )
		);

		$this->assertEquals(
			$expected,
			$instance->safeGet( $key )
		);
	}

	public function testDeepHasGet() {

		$defs = [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ]
		];

		$key = 'SOFTWARE';
		$expected = 'Foo';

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->setPropertyDefinitions(
			$defs
		);

		$this->assertTrue(
			$instance->deepHas( $key, 'id' )
		);

		$this->assertEquals(
			$expected,
			$instance->deepGet( $key, 'id' )
		);
	}

	public function testLocalDef() {

		$defs = [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ]
		];

		$key = 'SOFTWARE';
		$expected = 'Foo';

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->setLocalPropertyDefinitions(
			$defs
		);

		$this->assertTrue(
			$instance->isLocalDef( $key )
		);
	}

	public function testGetLabels() {

		$this->labelFetcher->expects( $this->once() )
			->method( 'getLabelsFrom' );

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->getLabels();
	}

	public function testGetLabel() {

		$this->labelFetcher->expects( $this->once() )
			->method( 'getLabel' );

		$instance = new PropertyDefinitions(
			$this->labelFetcher
		);

		$instance->getLabel( 'Foo' );
	}

}
