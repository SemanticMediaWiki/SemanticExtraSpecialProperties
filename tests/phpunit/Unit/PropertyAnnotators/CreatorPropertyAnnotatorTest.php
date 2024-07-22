<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\CreatorPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use User;
use WikiPage;

/**
 * @covers CreatorPropertyAnnotator
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

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
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

		$creator = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();

		$creator->expects( $this->once() )
			->method( 'getUserPage' )
			->willReturn( $userPage );

		$wikiPage = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getCreator' )
			->willReturn( $creator );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->willReturn( $wikiPage );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

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
