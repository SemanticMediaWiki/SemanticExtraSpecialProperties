<?php

namespace SESP\Tests;

use SESP\PropertyDefinitions;

/**
 * @covers \SESP\PropertyDefinitions
 * @group SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyDefinitionsTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PropertyDefinitions::class,
			new PropertyDefinitions()
		);
	}

	public function testEmptyFile() {

		$instance = new PropertyDefinitions();

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

		$instance = new PropertyDefinitions();

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

	public function testDeepHasGet() {

		$defs = [
			'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ]
		];

		$key = 'SOFTWARE';
		$expected = 'Foo';

		$instance = new PropertyDefinitions();

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

}
