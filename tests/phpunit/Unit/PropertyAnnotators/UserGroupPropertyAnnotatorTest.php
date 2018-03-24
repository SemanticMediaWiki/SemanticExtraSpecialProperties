<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\UserGroupPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\UserGroupPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class UserGroupPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USERGROUP' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			UserGroupPropertyAnnotator::class,
			new UserGroupPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new UserGroupPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider groupsProvider
	 */
	public function testAddAnnotation( $groups, $expected ) {

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getGroups' )
			->will( $this->returnValue( $groups ) );

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

		$instance = new UserGroupPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function groupsProvider() {

		$provider[] = [
			[],
			$this->never()
		];

		$provider[] = [
			[ 'Foo' ],
			$this->once()
		];

		return $provider;
	}

}
