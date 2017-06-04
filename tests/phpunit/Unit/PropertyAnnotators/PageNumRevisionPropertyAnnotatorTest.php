<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\PageNumRevisionPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\PageNumRevisionPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageNumRevisionPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___NREV' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PageNumRevisionPropertyAnnotator::class,
			new PageNumRevisionPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new PageNumRevisionPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$title->expects( $this->once() )
			->method( 'getArticleID' )
			->will( $this->returnValue( 1001 ) );

		$subject = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$connection = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->setMethods( array( 'estimateRowCount' ) )
			->getMock();

		$connection->expects( $this->once() )
			->method( 'estimateRowCount' )
			->will( $this->returnValue( 42 ) );

		$this->appFactory->expects( $this->once() )
			->method( 'getConnection' )
			->will( $this->returnValue( $connection ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageNumRevisionPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
