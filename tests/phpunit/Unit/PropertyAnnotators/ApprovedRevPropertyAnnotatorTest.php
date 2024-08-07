<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ApprovedRevPropertyAnnotator;
use SMW\DIProperty;
use SMWDINumber as DINumber;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedRevPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedRevPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___APPROVED' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ApprovedRevPropertyAnnotator::class,
			new ApprovedRevPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$annotator = new ApprovedRevPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				new DINumber( 42 ) );

		$annotator = new ApprovedRevPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedRev( 42 );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->property );

		$annotator = new ApprovedRevPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedRev( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
