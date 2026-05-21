<?php

namespace SESP\Tests\PropertyAnnotators;

use MWTimestamp;
use SESP\PropertyAnnotators\ApprovedDatePropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\Time;
use SMW\DataModel\SemanticData;
use SESP\AppFactory;
/**
 * @covers \SESP\PropertyAnnotators\ApprovedDatePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedDatePropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___APPROVEDDATE' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ApprovedDatePropertyAnnotator::class,
			new ApprovedDatePropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$annotator = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	protected static function getDITime( MWTimestamp $time ) {
		return new Time(
				Time::CM_GREGORIAN,
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
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				$time
			);

		$annotator = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedDate( $now );
		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->property );

		$annotator = new ApprovedDatePropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedDate( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
