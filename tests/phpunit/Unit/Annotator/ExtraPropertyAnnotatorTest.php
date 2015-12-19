<?php

namespace SESP\Tests\Annotator;

use SESP\Annotator\ExtraPropertyAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWDINumber as DINumber;

use Title;
use User;

/**
 * @covers \SESP\Annotator\ExtraPropertyAnnotator
 *
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ExtraPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $appFactory;

	protected function setup() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\Annotator\ExtraPropertyAnnotator',
			new ExtraPropertyAnnotator( $semanticData, $this->appFactory , $configuration )
		);
	}

	public function testNoAnnotationForSpecialPage() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Special', NS_SPECIAL ) ) );

		$configuration = array();

		$instance = new ExtraPropertyAnnotator(
			$semanticData,
			$this->appFactory,
			$configuration
		);

		$this->assertFalse(
			$instance->addAnnotation()
		);
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testPropertyAnnotationForEmptyConfigurationThrowsException() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( new DIWikiPage( 'Foo', NS_MAIN ) ) );

		$configuration = array();

		$instance = new ExtraPropertyAnnotator(
			$semanticData,
			$this->appFactory,
			$configuration
		);

		$this->setExpectedException( 'RuntimeException' );
		$instance->addAnnotation();
	}

	public function testPropertyAnnotation_CUSER() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getCreator' )
			->will( $this->returnValue( User::newFromName( 'Creator' ) ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_CUSER' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( '_CUSER' ),
			$semanticData->getProperties()
		);
	}

	public function testPropertyAnnotation_NREV() {

		$connection = $this->getMockBuilder( 'DatabaseMysql' )
			->disableOriginalConstructor()
			->getMock();

		$connection->expects( $this->once() )
			->method( 'estimateRowCount' )
			->with(
				$this->equalTo( 'revision' ),
				$this->equalTo( '*' ),
				$this->equalTo( array( 'rev_page' => 1001 ) ) )
			->will( $this->returnValue( 9999 ) );

		$title = $this->getMockBuilder( 'Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getArticleID' )
			->will( $this->returnValue( 1001 ) );

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newDatabaseConnection' )
			->will( $this->returnValue( $connection ) );

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_NREV' );
		$property = new DIProperty( $propertyId );

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_NREV' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9999 ), $value );
		}
	}

	public function testPropertyAnnotation_REVID() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getLatest' )
			->will( $this->returnValue( 9001 ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );
		$property = new DIProperty( $propertyId );

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_REVID' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9001 ), $value );
		}
	}

	public function testPropertyAnnotationWithNull_REVID() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getLatest' )
			->will( $this->returnValue( null ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_REVID' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertEmpty(
			$semanticData->getProperties()
		);
	}

	public function testPropertyAnnotation_USERREG() {

		$title = Title::newFromText( 'Foo', NS_USER );

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$this->appFactory->expects( $this->once() )
			->method( 'newUserFromTitle' )
			->will( $this->returnValue( $user ) );

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_USERREG' );
		$property = new DIProperty( $propertyId );

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_USERREG' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertInstanceOf( 'SMWDITime', $value );
		}
	}

	public function testNo_USERREG_AnnotationForUserSubpage() {

		$title = Title::newFromText( 'Foo/Boo', NS_USER );

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $page ) );

		$user = $this->getMockBuilder( '\User' )
			->disableOriginalConstructor()
			->getMock();

		$instance = $this->newExtraPropertyAnnotatorInstanceFor( '_USERREG' );

		$semanticData = $instance->getSemanticData();

		$this->assertEmpty(
			$semanticData->getProperties()
		);

		$this->assertTrue(
			$instance->addAnnotation()
		);

		$this->assertEmpty(
			$semanticData->getProperties()
		);
	}

	private function newExtraPropertyAnnotatorInstanceFor( $externalId ) {

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		$configuration = array(
			'sespSpecialProperties' => array( $externalId )
		);

		$instance = new ExtraPropertyAnnotator(
			$semanticData,
			$this->appFactory,
			$configuration
		);

		return $instance;
	}

}
