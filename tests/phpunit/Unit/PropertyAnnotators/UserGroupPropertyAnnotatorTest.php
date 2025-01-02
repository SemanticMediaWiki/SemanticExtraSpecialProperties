<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use SESP\PropertyAnnotators\UserGroupPropertyAnnotator;
use SMW\DIProperty;

/**
 * @covers \SESP\PropertyAnnotators\UserGroupPropertyAnnotator
 * @group semantic-extra-special-properties
 * @group Database
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class UserGroupPropertyAnnotatorTest extends MediaWikiIntegrationTestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		// Ensure the DIProperty is mocked or initialized correctly
		$this->property = $this->createMock( DIProperty::class );
		$this->property->method( 'getLabel' )->willReturn( '___USERGROUP' );
		$this->property->method( 'getKey' )->willReturn( UserGroupPropertyAnnotator::PROP_ID );
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
		// use MediaWikiIntegrationTestCase getTestUser() to create user with ID for testing purposes
		$user = $this->getTestUser( 'unittesters' )->getUser();

		foreach ( $groups as $group ) {
			MediaWikiServices::getInstance()
				->getUserGroupManager()
				->addUserToGroup( $user, $group );
		}

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->willReturn( $user );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->willReturn( true );

		$subject = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( $title );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

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
