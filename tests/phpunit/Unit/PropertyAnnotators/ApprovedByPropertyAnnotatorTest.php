<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ApprovedByPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use User;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedByPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedByPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___APPROVEDBY' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ApprovedByPropertyAnnotator::class,
			new ApprovedByPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$user = User::newFromName( "UnitTest" );
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->equalTo( $this->property ),
				$this->equalTo( DIWikiPage::newFromTitle( $user->getUserPage() ) ) );
		$instance = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedBy( $user );

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->equalTo( $this->property ) );

		$instance = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$instance->setApprovedBy( false );

		$instance->addAnnotation( $this->property, $semanticData );
	}
}
