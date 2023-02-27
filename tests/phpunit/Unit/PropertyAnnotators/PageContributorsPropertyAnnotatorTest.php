<?php

namespace SESP\Tests\PropertyAnnotators;

use ArrayIterator;
use MediaWiki\Permissions\PermissionManager;
use SESP\AppFactory;
use SESP\PropertyAnnotators\PageContributorsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use User;
use WikiPage;

/**
 * @covers \SESP\PropertyAnnotators\PageContributorsPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageContributorsPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___EUSER' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			PageContributorsPropertyAnnotator::class,
			new PageContributorsPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new PageContributorsPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$contributors = $this->getMockBuilder( ArrayIterator::class )
			->disableOriginalConstructor()
			->getMock();

		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getUserPage' )
			->will( $this->returnValue( $subject->getTitle() ) );

		$user->expects( $this->once() )
			->method( 'isAnon' )
			->will( $this->returnValue( false ) );

		$wikiPage = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$wikiPage->expects( $this->once() )
			->method( 'getContributors' )
			->will( $this->returnValue( $contributors ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromID' )
			->will( $this->returnValue( $user ) );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$permissionManager = $this->getMockBuilder( PermissionManager::class )
			->disableOriginalConstructor()
			->getMock();

		$permissionManager->expects( $this->once() )
			->method( 'getUserPermissions' )
			->will( $this->returnValue( [] ) );

		$instance = new PageContributorsPropertyAnnotator(
			$this->appFactory
		);

		$instance->setPermissionManager( $permissionManager );

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
