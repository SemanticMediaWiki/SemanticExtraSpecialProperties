<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\PageIDPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use TypeError;
use WikiPage;

/**
 * @covers \SESP\PropertyAnnotators\PageIDPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageIDPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___PAGEID' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PageIDPropertyAnnotator::class,
			new PageIDPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new PageIDPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider idProvider
	 */
	public function testAddAnnotation( $id, $expected, $throw ) {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$wikiPage = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		if ( $throw && version_compare( MW_VERSION, '1.36', '>=' ) ) {
			$this->expectException( TypeError::class );
		}

		$wikiPage->expects( $this->once() )
			->method( 'getId' )
			->will( $this->returnValue( $id ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new PageIDPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function idProvider() {

		$provider[] = [
			42,
			$this->once(),
			false
		];

		$provider[] = [
			0,
			$this->never(),
			false
		];

		$provider[] = [
			null,
			$this->never(),
			true
		];

		$provider[] = [
			'Foo',
			$this->never(),
			true
		];

		return $provider;
	}

}
