<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use SESP\AppFactory;
use SESP\PropertyAnnotators\UserGroupPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage as DIWikiPage;
use SMW\DataModel\SemanticData;

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

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		// Ensure the Property is mocked or initialized correctly
		$this->property = $this->createMock( Property::class );
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
