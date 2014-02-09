<?php

namespace SESP\Tests;

use SESP\PredefinedPropertyAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWDINumber as DINumber;

use Title;
use User;

/**
 * @covers \SESP\PredefinedPropertyAnnotator
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 */
class PredefinedPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function getClass() {
		return '\SESP\PredefinedPropertyAnnotator';
	}

	public function acquireInstance( $externalId ) {

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );
		$semanticData = new SemanticData( $subject );

		$configuration = array( 'sespSpecialProperties' => array( $externalId ) );

		$instance = new PredefinedPropertyAnnotator(
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
			$this->getClass(),
			new PredefinedPropertyAnnotator( $semanticData, $configuration )
		);
	}

	/**
	 * @depends testCanConstruct
	 */
	public function testAddAnnotationWithoutConfigurationThrowsException() {

		$this->setExpectedException( 'RuntimeException' );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$instance = new PredefinedPropertyAnnotator(
			$semanticData,
			$configuration
		);

		$instance->addAnnotation();
	}

	public function testAdd_CUSER_Annotation() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getCreator' )
			->will( $this->returnValue( User::newFromName( 'Creator' ) ) );

		$instance = $this->acquireInstance( '_CUSER' );
		$semanticData = $instance->getSemanticData();

		$instance->registerObject( 'WikiPage', function( $annotator ) use( $semanticData, $page ) {
			return $annotator->getSemanticData()->getSubject() === $semanticData->getSubject() ? $page : null;
		} );

		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( '_CUSER' ),
			$semanticData->getProperties()
		);
	}

	public function testAdd_NREV_Annotation() {

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

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_NREV' );
		$property = new DIProperty( $propertyId );

		$instance = $this->acquireInstance( '_NREV' );
		$semanticData = $instance->getSemanticData();

		$instance->registerObject( 'WikiPage', function( $annotator ) use( $semanticData, $page ) {
			return $annotator->getSemanticData()->getSubject() === $semanticData->getSubject() ? $page : null;
		} );

		$instance->registerObject( 'DBConnection', function() use( $connection ) {
			return $connection;
		} );

		$this->assertTrue( $instance->addAnnotation() );
		$this->assertArrayHasKey( $propertyId, $semanticData->getProperties() );

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9999 ), $value );
		}
	}

	public function testAdd_REVID_Annotation() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getId' )
			->will( $this->returnValue( 9001 ) );

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );
		$property = new DIProperty( $propertyId );

		$instance = $this->acquireInstance( '_REVID' );
		$semanticData = $instance->getSemanticData();

		$instance->registerObject( 'WikiPage', function( $annotator ) use( $semanticData, $page ) {
			return $annotator->getSemanticData()->getSubject() === $semanticData->getSubject() ? $page : null;
		} );

		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			$propertyId,
			$semanticData->getProperties()
		);

		foreach ( $semanticData->getPropertyValues( $property ) as $value ) {
			$this->assertEquals( new DINumber( 9001 ), $value );
		}
	}

	public function test_REVID_NoAnnotationWhenNull() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->once() )
			->method( 'getId' )
			->will( $this->returnValue( null ) );

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );

		$instance = $this->acquireInstance( '_REVID' );
		$semanticData = $instance->getSemanticData();

		$instance->registerObject( 'WikiPage', function( $annotator ) use( $semanticData, $page ) {
			return $annotator->getSemanticData()->getSubject() === $semanticData->getSubject() ? $page : null;
		} );

		$this->assertTrue( $instance->addAnnotation() );
		$this->assertEmpty( $semanticData->getProperties() );
	}

}
