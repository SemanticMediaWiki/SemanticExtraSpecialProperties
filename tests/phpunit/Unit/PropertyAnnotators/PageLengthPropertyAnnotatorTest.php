<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Title\Title;
use SESP\PropertyAnnotators\PageLengthPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;
use SESP\AppFactory;
use SMW\DataItems\WikiPage as DIWikiPage;
/**
 * @covers \SESP\PropertyAnnotators\PageLengthPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class PageLengthPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___PAGELGTH' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			PageLengthPropertyAnnotator::class,
			new PageLengthPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new PageLengthPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider lengthProvider
	 */
	public function testAddAnnotation( $length, $expected ) {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getLength' )
			->willReturn( $length );

		$subject = $this->getMockBuilder( DIWikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( $title );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new PageLengthPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function lengthProvider() {
		$provider[] = [
			42,
			$this->once()
		];

		$provider[] = [
			null,
			$this->never()
		];

		$provider[] = [
			'Foo',
			$this->never()
		];

		return $provider;
	}

}
