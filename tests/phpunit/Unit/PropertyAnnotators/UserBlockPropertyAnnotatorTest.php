<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\UserBlockPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\UserBlockPropertyAnnotator
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

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
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

		$block = $this->getMockBuilder( '\Block' )
			->disableOriginalConstructor()
			->getMock();

		$block->expects( $this->any() )
			->method( 'prevents' )
			->will( $this->returnCallback( $compare ) );

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getBlock' )
			->will( $this->returnValue( $block ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->will( $this->returnValue( $user ) );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->will( $this->returnValue( true ) );

		$subject = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
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
