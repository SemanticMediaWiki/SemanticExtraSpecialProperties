<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\PageContributorsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

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

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
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

		$contributors = $this->getMockBuilder( '\ArrayIterator' )
			->disableOriginalConstructor()
			->getMock();

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$user->expects( $this->once() )
			->method( 'getUserPage' )
			->will( $this->returnValue( $subject->getTitle() ) );

		$user->expects( $this->once() )
			->method( 'isAnon' )
			->will( $this->returnValue( false ) );

		$user->expects( $this->once() )
			->method( 'getRights' )
			->will( $this->returnValue( [] ) );

		$wikiPage = $this->getMockBuilder( '\WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getContributors' )
			->will( $this->returnValue( $contributors ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromID' )
			->will( $this->returnValue( $user ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new PageContributorsPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
