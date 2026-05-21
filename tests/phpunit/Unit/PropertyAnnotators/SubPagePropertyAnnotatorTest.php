<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Title\Title;
use SESP\PropertyAnnotators\SubPagePropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
/**
 * @covers \SESP\PropertyAnnotators\SubPagePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class SubPagePropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___SUBP' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			SubPagePropertyAnnotator::class,
			new SubPagePropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new SubPagePropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$sub = WikiPage::newFromText( __METHOD__ )->getTitle();

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getSubpages' )
			->willReturn( [ $sub ] );

		$subject = $this->getMockBuilder( '\SMW\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( $title );

		$semanticData = $this->getMockBuilder( '\SMW\DataModel\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new SubPagePropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
