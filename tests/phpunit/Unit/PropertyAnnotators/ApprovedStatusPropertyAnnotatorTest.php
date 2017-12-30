<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ApprovedStatusPropertyAnnotator;
use SMW\DIProperty;
use SMWDIString as DIString;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedStatusPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedStatusPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___APPROVEDSTATUS' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ApprovedStatusPropertyAnnotator::class,
			new ApprovedStatusPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->equalTo( $this->property ),
				$this->equalTo( new DIString( "checkme" ) ) );

		$instance = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedStatus( "checkme" );

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->equalTo( $this->property ) );

		$instance = new ApprovedStatusPropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedStatus( false );

		$instance->addAnnotation( $this->property, $semanticData );
	}
}
