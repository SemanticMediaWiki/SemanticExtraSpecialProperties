<?php

namespace SESP\Tests;

use SESP\LabelFetcher;

/**
 * @covers \SESP\LabelFetcher
 * @group SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class LabelFetcherTest extends \PHPUnit_Framework_TestCase {

	private $cache;

	protected function setup() {
		parent::setUp();

		$this->cache = $this->getMockBuilder( '\Onoi\Cache\Cache' )
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

		$this->assertInternalType(
			'string',
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

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->will( $this->returnValue( false ) );

		$this->cache->expects( $this->once() )
			->method( 'save' );

		$instance = new LabelFetcher(
			$this->cache
		);

		$this->assertInternalType(
			'array',
			$instance->getLabelsFrom( $propertyDefinitions )
		);
	}

	public function testGetLabelsCached() {

		$labels = [
			'FOO' => 'Bar'
		];

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( [ 'getLabels' ] )
			->getMock();

		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->will( $this->returnValue( $labels ) );

		$this->cache->expects( $this->never() )
			->method( 'save' );

		$instance = new LabelFetcher(
			$this->cache
		);

		$instance->getLabelsFrom( $propertyDefinitions );
	}

	public function testGetLabelsCachedVersioned() {

		$labels = [
			'FOO' => 'Bar'
		];

		$propertyDefinitions = $this->getMockBuilder( '\SESP\PropertyDefinitions' )
			->disableOriginalConstructor()
			->setMethods( null )
			->getMock();

		$this->cache->expects( $this->once() )
			->method( 'fetch' )
			->with( $this->stringContains( 'sesp:labels:e1484da79bc6323bcb087894cf9cab03' ) )
			->will( $this->returnValue( $labels ) );

		$instance = new LabelFetcher(
			$this->cache
		);

		$instance->setLabelCacheVersion(
			2
		);

		$instance->getLabelsFrom( $propertyDefinitions );
	}

}
