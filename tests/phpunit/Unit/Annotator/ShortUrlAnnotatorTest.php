<?php

namespace SESP\Tests\Annotator;

use SESP\Annotator\ShortUrlAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;

use Title;

/**
 * @uses \SESP\Annotator\ShortUrlAnnotator
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ShortUrlAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\Annotator\ShortUrlAnnotator',
			new ShortUrlAnnotator( $semanticData, $configuration )
		);
	}

	public function testMissingShortUrlUtilsThrowsException() {

		$this->setExpectedException( 'RuntimeException' );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$instance = $this->getMock( '\SESP\Annotator\ShortUrlAnnotator',
			array( 'hasShortUrlUtils' ),
			array(
				$semanticData,
				$configuration
			)
		);

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->will( $this->returnValue( false ) );

		$instance->addAnnotation();
	}

	public function testAddAnnotationOnMockShortUrl() {

		$title = Title::newFromText( __METHOD__ );
		$semanticData = new SemanticData( DIWikiPage::newFromTitle( $title ) );

		$configuration = array();

		$instance = $this->getMock( '\SESP\Annotator\ShortUrlAnnotator',
			array( 'hasShortUrlUtils', 'getShortUrl' ),
			array(
				$semanticData,
				$configuration
			)
		);

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->will( $this->returnValue( true ) );

		$instance->expects( $this->once() )
			->method( 'getShortUrl' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'example.org' ) );

		$instance->addAnnotation();

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( '_SHORTURL' ),
			$semanticData->getProperties()
		);
	}

}
