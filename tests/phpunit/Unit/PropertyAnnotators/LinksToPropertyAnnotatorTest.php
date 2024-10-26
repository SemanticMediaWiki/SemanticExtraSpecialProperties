<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\LinksToPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use Title;

/**
 * @covers \SESP\PropertyAnnotators\LinksToPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 */
class LinksToPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___LINKSTO' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			LinksToPropertyAnnotator::class,
			new LinksToPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new LinksToPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$factory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$factory->expects( $this->once() )->method( 'getConnection' );

		$subject = $this->getMockBuilder( DIWikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( 'Foo' ) );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$annotator = new LinksToPropertyAnnotator(
			$factory
		);

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testNotAddAnnotation() {
		$factory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$factory->expects( $this->never() )
			->method( 'getConnection' );

		$subject = $this->getMockBuilder( DIWikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( Title::newFromText( 'Foo' ) );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$annotator = new LinksToPropertyAnnotator(
			$factory
		);

		$annotator->setEnabledNamespaces( [ 2 ] );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
