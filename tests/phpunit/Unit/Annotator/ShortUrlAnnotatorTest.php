<?php

namespace SESP\Tests\Annotator;

use SESP\Annotator\ShortUrlAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;

use Title;

/**
 * @covers \SESP\Annotator\ShortUrlAnnotator
 *
 * @group semantic-extra-special-properties
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

		$this->assertInstanceOf(
			'\SESP\Annotator\ShortUrlAnnotator',
			new ShortUrlAnnotator( $semanticData )
		);
	}

	public function testCanUseShortUrl() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new ShortUrlAnnotator( $semanticData );

		$this->assertInternalType(
			'boolean',
			$instance->canUseShortUrl()
		);
	}

	public function testMissingShortUrlUtilsThrowsException() {

		$this->setExpectedException( 'RuntimeException' );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$instance = $this->getMock( '\SESP\Annotator\ShortUrlAnnotator',
			array( 'hasShortUrlUtils' ),
			array(
				$semanticData
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

		$instance = $this->getMock( '\SESP\Annotator\ShortUrlAnnotator',
			array( 'hasShortUrlUtils', 'getShortUrl' ),
			array(
				$semanticData
			)
		);

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->will( $this->returnValue( true ) );

		$instance->expects( $this->once() )
			->method( 'getShortUrl' )
			->with( $this->equalTo( $title ) )
			->will( $this->returnValue( 'example.org' ) );

		$instance->setShortUrlPrefix( 'foo' );
		$instance->addAnnotation();

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( '_SHORTURL' ),
			$semanticData->getProperties()
		);
	}

}
