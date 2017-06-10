<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\PageViewsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\PageViewsPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageViewsPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___VIEWS' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PageViewsPropertyAnnotator::class,
			new PageViewsPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new PageViewsPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testDisableCounters() {

		$this->appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( $this->equalTo( 'wgDisableCounters' ) )
			->will( $this->returnValue( true ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->never() )
			->method( 'getSubject' );

		$instance = new PageViewsPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testAddAnnotation() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( $this->equalTo( 'wgDisableCounters' ) )
			->will( $this->returnValue( false ) );

		$wikiPage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->setMethods( [ 'getCount' ] )
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getCount' )
			->will( $this->returnValue( 42 ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageViewsPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
