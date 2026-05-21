<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\NullPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @covers \SESP\PropertyAnnotators\NullPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class NullPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( 'Foo' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			NullPropertyAnnotator::class,
			new NullPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new NullPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$instance = new NullPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
