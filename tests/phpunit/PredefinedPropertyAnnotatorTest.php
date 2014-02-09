<?php

namespace SESP\Tests;

use SESP\PredefinedPropertyAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;

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
	public function testAddAnnotationThrowsException() {

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

	public function testAddPropertyValues_CUSER() {

		$page = $this->getMockBuilder( 'WikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$page->expects( $this->any() )
			->method( 'getCreator' )
			->will( $this->returnValue( User::newFromName( 'Creator' ) ) );

		$subject = DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) );
		$semanticData = new SemanticData( $subject );

		$configuration = array( 'sespSpecialProperties' => array( '_CUSER' ) );

		$instance = new PredefinedPropertyAnnotator(
			$semanticData,
			$configuration
		);

		$instance->registerObject( 'WikiPage', function( $annotator ) use( $subject, $page ) {
			return $annotator->getSemanticData()->getSubject() === $subject ? $page : null;
		} );

		$this->assertTrue( $instance->addAnnotation() );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( '_CUSER' ),
			$semanticData->getProperties()
		);
	}

}
