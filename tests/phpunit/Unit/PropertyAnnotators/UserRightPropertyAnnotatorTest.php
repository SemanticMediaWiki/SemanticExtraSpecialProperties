<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Title\Title;
use SESP\AppFactory;
use SESP\PropertyAnnotators\UserRightPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage as DIWikiPage;
use SMW\DataModel\SemanticData;
use User;

/**
 * @covers \SESP\PropertyAnnotators\UserRightPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class UserRightPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___USERRIGHT' );
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
		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();

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

		$permissionManager = $this->getMockBuilder( PermissionManager::class )
			->disableOriginalConstructor()
			->getMock();

		$permissionManager->expects( $this->once() )
			->method( 'getUserPermissions' )
			->willReturn( $rights );

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
