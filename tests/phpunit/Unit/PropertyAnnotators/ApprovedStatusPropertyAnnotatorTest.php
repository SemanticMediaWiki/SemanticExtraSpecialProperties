<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\ApprovedStatusPropertyAnnotator;
use SMW\DataItems\Blob as DIString;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedStatusPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedStatusPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___APPROVEDSTATUS' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ApprovedStatusPropertyAnnotator::class,
			new ApprovedStatusPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$annotator = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				new DIString( "checkme" ) );

		$annotator = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedStatus( "checkme" );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->property );

		$annotator = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedStatus( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
