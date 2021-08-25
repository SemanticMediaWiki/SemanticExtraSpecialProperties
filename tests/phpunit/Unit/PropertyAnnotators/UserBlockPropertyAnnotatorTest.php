<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Block\DatabaseBlock;
use SESP\AppFactory;
use SESP\PropertyAnnotators\UserBlockPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use Title;
use User;

/**
 * @covers UserBlockPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class UserBlockPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USERBLOCK' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			UserBlockPropertyAnnotator::class,
			new UserBlockPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new UserBlockPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider blockActionProvider
	 */
	public function testAddAnnotation( $action, $expected ) {

		$compare = function( $reason ) use( $action ) {
			return $reason == $action;
		};

		$block = $this->getMockBuilder( DatabaseBlock::class )
			->disableOriginalConstructor()
			->getMock();

		$block->expects( $this->any() )
			->method( 'appliesToRight' )
			->will( $this->returnCallback( $compare ) );

		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getBlock' )
			->will( $this->returnValue( $block ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->will( $this->returnValue( $user ) );

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->will( $this->returnValue( true ) );

		$subject = $this->getMockBuilder( DIWikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new UserBlockPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function blockActionProvider() {

		$provider[] = [
			'Foo',
			$this->never()
		];

		$provider[] = [
			'edit',
			$this->once()
		];

		$provider[] = [
			'createaccount',
			$this->once()
		];

		$provider[] = [
			'sendemail',
			$this->once()
		];

		$provider[] = [
			'editownusertalk',
			$this->once()
		];

		$provider[] = [
			'read',
			$this->once()
		];

		return $provider;
	}

}
