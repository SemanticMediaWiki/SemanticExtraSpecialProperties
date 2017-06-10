<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\CreatorPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\CreatorPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class CreatorPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___CUSER' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			CreatorPropertyAnnotator::class,
			new CreatorPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new CreatorPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider userPageProvider
	 */
	public function testAddAnnotation( $userPage, $expected ) {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$creator = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$creator->expects( $this->once() )
			->method( 'getUserPage' )
			->will( $this->returnValue( $userPage ) );

		$wikiPage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getCreator' )
			->will( $this->returnValue( $creator ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new CreatorPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function userPageProvider() {

		$provider[] = [
			DIWikiPage::newFromText( __METHOD__ )->getTitle(),
			$this->once()
		];

		$provider[] = [
			0,
			$this->never()
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
