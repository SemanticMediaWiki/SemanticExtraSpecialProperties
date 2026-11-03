<?php

namespace SESP\Tests;

use SESP\LabelFetcher;
use SESP\PropertyDefinitions;

/**
 * @covers \SESP\PropertyDefinitions
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyDefinitionsTest extends \PHPUnit\Framework\TestCase {

	private $labelFetcher;
	private $definitionsFile;

	protected function setUp(): void {
		parent::setUp();

		$this->labelFetcher = $this->getMockBuilder( LabelFetcher::class )
			->disableOriginalConstructor()
			->getMock();
	}

	protected function tearDown(): void {
		if ( $this->definitionsFile !== null ) {
			unlink( $this->definitionsFile );
			$this->definitionsFile = null;
		}

		parent::tearDown();
	}

	/**
	 * Instance that reads its definitions from a file and has not been iterated yet.
	 */
	private function newInstanceReadingFromFile(): PropertyDefinitions {
		$this->definitionsFile = tempnam( sys_get_temp_dir(), 'sesp' );

		file_put_contents(
			$this->definitionsFile,
			json_encode( [ 'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ] ] )
		);

		return new PropertyDefinitions( $this->labelFetcher, $this->definitionsFile );
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

	public function testHasAndGetLoadTheDefinitionsFileOnDemand() {
		$instance = $this->newInstanceReadingFromFile();

		$this->assertTrue( $instance->has( 'SOFTWARE' ) );
		$this->assertSame( [ 'id' => 'Foo', 'type' => '_txt' ], $instance->get( 'SOFTWARE' ) );
	}

	public function testDeepHasAndDeepGetLoadTheDefinitionsFileOnDemand() {
		$instance = $this->newInstanceReadingFromFile();

		$this->assertTrue( $instance->deepHas( 'SOFTWARE', 'id' ) );
		$this->assertSame( 'Foo', $instance->deepGet( 'SOFTWARE', 'id' ) );
	}

	public function testSafeGetLoadsTheDefinitionsFileOnDemand() {
		$instance = $this->newInstanceReadingFromFile();

		$this->assertSame(
			[ 'id' => 'Foo', 'type' => '_txt' ],
			$instance->safeGet( 'SOFTWARE', 'default' )
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
