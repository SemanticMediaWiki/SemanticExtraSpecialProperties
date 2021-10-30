<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\UserEditCountPerNsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\UserEditCountPerNsPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author Alexander Mashin
 */
class UserEditCountPerNsPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USEREDITCNTNS' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			UserEditCountPerNsPropertyAnnotator::class,
			new UserEditCountPerNSPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new UserEditCountPerNsPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider editCountPerNsProvider
	 */
	public function testGetEditsPerNs() {

	}

	/**
	 * @dataProvider editCountPerNsProvider
	 */
	public function testAddAnnotation( $count, $expected ) {

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getEditsPerNs' )->willReturn( [ 0 => $count ] );

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

		$instance = new UserEditCountPerNsPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function editCountPerNsProvider() {

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

