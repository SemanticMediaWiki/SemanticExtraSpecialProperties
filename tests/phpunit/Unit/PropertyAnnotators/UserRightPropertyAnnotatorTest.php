<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Permissions\PermissionManager;
use SESP\PropertyAnnotators\UserRightPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\UserRightPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class UserRightPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USERRIGHT' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			UserRightPropertyAnnotator::class,
			new UserRightPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new UserRightPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider rightsProvider
	 */
	public function testAddAnnotation( $rights, $expected ) {

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->will( $this->returnValue( $user ) );

		$this->appFactory->expects( $this->once() )
			->method( 'getUserRights' )
			->will( $this->returnValue( $rights ) );

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

		$permissionManager = $this->getMockBuilder( PermissionManager::class )
			->disableOriginalConstructor()
			->getMock();

		$permissionManager->expects( $this->once() )
			->method( 'getUserPermissions' )
			->will( $this->returnValue( $rights ) );

		$instance = new UserRightPropertyAnnotator(
			$this->appFactory
		);

		$instance->setPermissionManager( $permissionManager );

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function rightsProvider() {

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
