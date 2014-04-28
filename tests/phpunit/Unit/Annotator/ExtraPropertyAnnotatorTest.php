<?php

namespace SESP\Tests\Annotator;

use SESP\Annotator\ExtraPropertyAnnotator;
use SESP\PropertyRegistry;
use SESP\DIC\ObjectFactory;

use SMW\SemanticData;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWDINumber as DINumber;

use Title;
use User;

/**
 * @uses \SESP\Annotator\ExtraPropertyAnnotator
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ExtraPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	protected $objectFactory;
	protected $propertyRegistry;

	protected function setUp() {
		parent::setUp();

		$this->objectFactory = ObjectFactory::getInstance();
		$this->propertyRegistry = PropertyRegistry::getInstance();
	}

	protected function tearDown() {
		$this->objectFactory->clear();
		$this->propertyRegistry->clear();

		parent::tearDown();
	}

	public function acquireInstance( $externalId ) {

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		$configuration = array(
			'sespSpecialProperties' => array( $externalId )
		);

		$instance = new ExtraPropertyAnnotator(
			$semanticData,
			$configuration
		);

		return $instance;
	}

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\Annotator\ExtraPropertyAnnotator',
			new ExtraPropertyAnnotator( $semanticData, $configuration )
		);
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testPropertyAnnotationWithoutConfigurationThrowsException() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$instance = new ExtraPropertyAnnotator(
			$semanticData,
			$configuration
		);

		$this->setExpectedException( 'RuntimeException' );

		$instance->addAnnotation();
	}

	public function testPropertyAnnotationForCUSER() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getCreator' )
			->will( $this->returnValue( User::newFromName( 'Creator' ) ) );

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$instance = $this->acquireInstance( '_CUSER' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			$this->propertyRegistry->getPropertyId( '_CUSER' ),
			$semanticData->getProperties()
		);
	}

	public function testPropertyAnnotationForNREV() {

		$connection = $this->getMockBuilder( 'DatabaseBase' )
			->disableOriginalConstructor()
			->setMethods( array( 'estimateRowCount' ) )
			->getMockForAbstractClass();

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

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$this->objectFactory->registerObject(
			'mw.dbconnection',
			$connection
		);

		$propertyId = $this->propertyRegistry->getPropertyId( '_NREV' );
		$property = new DIProperty( $propertyId );

		$instance = $this->acquireInstance( '_NREV' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey( $propertyId, $semanticData->getProperties() );

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9999 ), $value );
		}
	}

	public function testPropertyAnnotationForREVID() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getLatest' )
			->will( $this->returnValue( 9001 ) );

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$propertyId = $this->propertyRegistry->getPropertyId( '_REVID' );
		$property = new DIProperty( $propertyId );

		$instance = $this->acquireInstance( '_REVID' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9001 ), $value );
		}
	}

	public function testPropertyAnnotationWithNullRevisionForREVID() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getLatest' )
			->will( $this->returnValue( null ) );

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$instance = $this->acquireInstance( '_REVID' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );
		$this->assertEmpty( $semanticData->getProperties() );
	}

	public function testPropertyAnnotationForUSERREG() {

		$title = Title::newFromText( 'Foo', NS_USER );

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$propertyId = $this->propertyRegistry->getPropertyId( '_USERREG' );
		$property = new DIProperty( $propertyId );

		$instance = $this->acquireInstance( '_USERREG' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertInstanceOf( 'SMWDITime', $value );
		}
	}

	public function testPropertyAnnotationOnUserSubpageForUSERREG() {

		$title = Title::newFromText( 'Foo/Boo', NS_USER );

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->atLeastOnce() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->objectFactory->registerObject(
			'mw.wikipage',
			$this->attachWikiPage( $page )
		);

		$instance = $this->acquireInstance( '_USERREG' );
		$semanticData = $instance->getSemanticData();

		$this->assertEmpty( $semanticData->getProperties() );
		$this->assertTrue( $instance->addAnnotation() );
		$this->assertEmpty( $semanticData->getProperties() );
	}

	protected function attachWikiPage( $page ) {
		return function( $dependencyBuilder ) use( $page ) {
			return $dependencyBuilder->getArgumentValue( 'mw.title' ) ? $page : null;
		};
	}

}
