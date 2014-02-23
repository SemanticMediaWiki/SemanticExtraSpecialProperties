<?php

namespace SESP\Tests;

use SESP\ShortUrlAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;

use Title;

/**
 * @covers \SESP\ShortUrlAnnotator
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
class ShortUrlAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\ShortUrlAnnotator',
			new ShortUrlAnnotator( $semanticData, $configuration )
		);
	}

	public function testMissingShortUrlUtilsThrowsException() {

		$this->setExpectedException( 'RuntimeException' );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$instance = $this->getMock( '\SESP\ShortUrlAnnotator',
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

		$instance = $this->getMock( '\SESP\ShortUrlAnnotator',
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
