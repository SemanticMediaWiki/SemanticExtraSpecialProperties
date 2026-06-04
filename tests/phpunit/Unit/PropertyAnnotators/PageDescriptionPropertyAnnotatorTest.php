<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\PageDescriptionPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage as DIWikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SESP\PropertyAnnotators\PageDescriptionPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class PageDescriptionPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___DESCRIPTION' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			PageDescriptionPropertyAnnotator::class,
			new PageDescriptionPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new PageDescriptionPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->appFactory->expects( $this->once() )
			->method( 'getPageProperty' )
			->with( $this->anything(), 'description' )
			->willReturn( 'A short summary of the page.' );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageDescriptionPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testAddAnnotationIsSkippedWhenNoDescription() {
		$subject = DIWikiPage::newFromText( __METHOD__ );

		$this->appFactory->expects( $this->once() )
			->method( 'getPageProperty' )
			->willReturn( null );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageDescriptionPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
