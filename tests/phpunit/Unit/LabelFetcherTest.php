<?php

namespace SESP\Tests;

use SESP\LabelFetcher;
use SESP\PropertyDefinitions;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @covers \SESP\LabelFetcher
 * @group SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class LabelFetcherTest extends \PHPUnit\Framework\TestCase {

	private $cache;

	protected function setUp(): void {
		parent::setUp();

		$this->cache = $this->getMockBuilder( BagOStuff::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			LabelFetcher::class,
			new LabelFetcher( $this->cache )
		);
	}

	public function testGetLabel() {
		$instance = new LabelFetcher(
			$this->cache
		);

		$this->assertIsString(
			$instance->getLabel( 'Foo' )
		);
	}

	public function testGetLabelsUnCached() {
		$defs = [
			'FOO' => [ 'id' => 'Foo', 'alias' => 'Foo' ],
			'_EXIF' => [
				'BAR' => [ 'id' => 'Bar', 'alias' => 'Bar' ]
			]
		];

		$propertyDefinitions = $this->getMockBuilder( PropertyDefinitions::class )
			->disableOriginalConstructor()
			->addMethods( [] )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$this->cache->expects( $this->once() )
			->method( 'get' )
			->willReturn( false );

		$this->cache->expects( $this->once() )
			->method( 'set' );

		$instance = new LabelFetcher(
			$this->cache
		);

		$this->assertIsArray(
			$instance->getLabelsFrom( $propertyDefinitions )
		);
	}

	public function testGetLabelsCached() {
		$labels = [
			'FOO' => 'Bar'
		];

		$propertyDefinitions = $this->getMockBuilder( PropertyDefinitions::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getLabels' ] )
			->getMock();

		$this->cache->expects( $this->once() )
			->method( 'get' )
			->willReturn( $labels );

		$this->cache->expects( $this->never() )
			->method( 'set' );

		$instance = new LabelFetcher(
			$this->cache
		);

		$instance->getLabelsFrom( $propertyDefinitions );
	}

	public function testGetLabelsCachedVersioned() {
		$labels = [
			'FOO' => 'Bar'
		];

		$propertyDefinitions = $this->getMockBuilder( PropertyDefinitions::class )
			->disableOriginalConstructor()
			->onlyMethods( [] )
			->getMock();

		$this->cache->expects( $this->once() )
			->method( 'get' )
			->with( $this->stringContains( 'sesp:labels:e1484da79bc6323bcb087894cf9cab03' ) )
			->willReturn( $labels );

		$instance = new LabelFetcher(
			$this->cache
		);

		$instance->setLabelCacheVersion(
			2
		);

		$instance->getLabelsFrom( $propertyDefinitions );
	}

}
