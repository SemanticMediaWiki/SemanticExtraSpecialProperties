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
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedByPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
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
		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
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
				$this->property,
				DIWikiPage::newFromTitle( $user->getUserPage() ) );
		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedBy( $user );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->property );

		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedBy( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
