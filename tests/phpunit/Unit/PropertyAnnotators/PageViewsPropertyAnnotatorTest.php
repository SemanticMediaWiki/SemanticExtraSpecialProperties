<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\PageViewsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use WikiPage;

/**
 * @covers PageViewsPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageViewsPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
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
			->with( 'wgDisableCounters' )
			->willReturn( true );

		$semanticData = $this->getMockBuilder( SemanticData::class )
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
			->with( 'wgDisableCounters' )
			->willReturn( false );

		$wikiPage = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->addMethods( [ 'getCount' ] )
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getCount' )
			->willReturn( 42 );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->willReturn( $wikiPage );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageViewsPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
