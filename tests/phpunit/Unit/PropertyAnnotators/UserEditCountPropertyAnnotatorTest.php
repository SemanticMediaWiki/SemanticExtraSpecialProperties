<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\UserEditCountPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use User;
use Title;
use WikiPage;

/**
 * @covers UserEditCountPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class UserEditCountPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USEREDITCNT' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			UserEditCountPropertyAnnotator::class,
			new UserEditCountPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new UserEditCountPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider editCountProvider
	 */
	public function testAddAnnotation( $count, $expected ) {
		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getEditCount' )
			->willReturn( $count );

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->willReturn( $user );

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->willReturn( true );

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

		$instance = new UserEditCountPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function editCountProvider() {
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
