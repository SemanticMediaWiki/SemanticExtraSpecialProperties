<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ApprovedDatePropertyAnnotator;
use SMW\DIProperty;
use MWTimestamp;
use SMWDITime as DITime;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedDatePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedDatePropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___APPROVEDDATE' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ApprovedDatePropertyAnnotator::class,
			new ApprovedDatePropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	protected static function getDITime( MWTimestamp $time ) {
		return new DITime(
				DITime::CM_GREGORIAN,
				$time->format( 'Y' ),
				$time->format( 'm' ),
				$time->format( 'd' ),
				$time->format( 'H' ),
				$time->format( 'i' )
		);
	}

	public function testAddAnnotation() {
		$now = new MWTimestamp( wfTimestampNow() );
		$time = self::getDITime( $now );
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->equalTo( $this->property ),
				$this->equalTo( $time )
			);

		$instance = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedDate( $now );
		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->equalTo( $this->property ) );

		$instance = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedDate( false );

		$instance->addAnnotation( $this->property, $semanticData );
	}
}
