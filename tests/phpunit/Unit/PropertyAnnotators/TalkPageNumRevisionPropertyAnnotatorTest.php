<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\TalkPageNumRevisionPropertyAnnotator;
use SMW\DIProperty;

/**
 * @covers \SESP\PropertyAnnotators\TalkPageNumRevisionPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class TalkPageNumRevisionPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___NTREV' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			TalkPageNumRevisionPropertyAnnotator::class,
			new TalkPageNumRevisionPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new TalkPageNumRevisionPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider rowCountProvider
	 */
	public function testAddAnnotation( $count, $expected ) {
		$talkPage = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$talkPage->expects( $this->once() )
			->method( 'exists' )
			->willReturn( true );

		$talkPage->expects( $this->once() )
			->method( 'getArticleID' )
			->willReturn( 1001 );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getTalkPage' )
			->willReturn( $talkPage );

		$subject = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( $title );

		$connection = $this->getMockBuilder( '\stdClass' )
			->disableOriginalConstructor()
			->addMethods( [ 'estimateRowCount' ] )
			->getMock();

		$connection->expects( $this->once() )
			->method( 'estimateRowCount' )
			->willReturn( $count );

		$this->appFactory->expects( $this->once() )
			->method( 'getConnection' )
			->willReturn( $connection );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new TalkPageNumRevisionPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function rowCountProvider() {
		$provider[] = [
			42,
			$this->once()
		];

		$provider[] = [
			0,
			$this->never()
		];

		$provider[] = [
			40,
			$this->once()
		];

		$provider[] = [
			44,
			$this->once()
		];

		return $provider;
	}

}
