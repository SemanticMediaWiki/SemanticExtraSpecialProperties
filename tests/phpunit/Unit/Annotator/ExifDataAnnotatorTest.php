<?php

namespace SESP\Tests\Annotator;

use SESP\Annotator\ExifDataAnnotator;
use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIWikiPage;
use SMW\DIProperty;

use Title;

/**
 * @covers \SESP\Annotator\ExifDataAnnotator
 *
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ExifDataAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( '\File' )
			->disableOriginalConstructor()
			->getMock();

		$this->assertInstanceOf(
			'\SESP\Annotator\ExifDataAnnotator',
			new ExifDataAnnotator( $semanticData, $file )
		);
	}

	public function testTryAddAnnotationForNonExistingFile() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( false ) );

		$file->expects( $this->never() )
			->method( 'getMetadata' );

		$instance = new ExifDataAnnotator( $semanticData, $file );
		$instance->addAnnotation();
	}

	public function testPropertyAnnotationOnEmptyExifData() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( false );

		$this->assertEmpty(
			$semanticData->findSubSemanticData( '_EXIFDATA' )
		);
	}

	public function testPropertyAnnotationWithBlobValue() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( array(
			'Software'  => 'ABC',
		) );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'Software' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);
	}

	public function testPropertyAnnotationWithFullDateTime() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( array(
			'DateTimeOriginal'  => '2013:01:11 02:13:35',
		) );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'DateTimeOriginal' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);
	}

	public function testPropertyAnnotationWithoutSeconds() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( array(
			'DateTimeOriginal'  => '2013:01:11 02:13',
		) );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'DateTimeOriginal' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);
	}

	public function testPropertyAnnotationWithDateOnly() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( array(
			'DateTimeOriginal'  => '2013:01:11',
		) );

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'DateTimeOriginal' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);
	}

	public function testPropertyAnnotationWithInvalidDate() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy( array(
			'DateTimeOriginal'  => '0000:00:00 00:00:00',
			'DateTime'  => '    :  :     :  :  ',
		) );

		$this->assertEmpty(
			$semanticData->findSubSemanticData( '_EXIFDATA' )
		);
	}

	public function testPropertyAnnotationWithNumberValue() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy(
			array(
				'Foo'  => 'ABC',
			),
			array(
				'getWidth'  => 1000,
				'getHeight' => 9999
			)
		);

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'ImageWidth' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);

		$this->assertArrayHasKey(
			PropertyRegistry::getInstance()->getPropertyId( 'ImageLength' ),
			$semanticData->findSubSemanticData( '_EXIFDATA' )->getProperties()
		);

	}

	public function testPropertyAnnotationWithUnkownExif() {

		$semanticData = $this->getSemanticDataForExifDataAnnotatorBy(
			array(
				'Foo'       => 'ABC',
			),
			array(
				'getWidth'  => false,
				'getHeight' => false
			)
		);

		$this->assertEmpty(
			$semanticData->findSubSemanticData( '_EXIFDATA' )
		);
	}

	protected function getSemanticDataForExifDataAnnotatorBy( $exifData, $parameters = array() ) {

		$expectedToRun = $exifData ? $this->atLeastOnce() : $this->never();

		$exifData = serialize( $exifData );

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$file->expects( $this->once() )
			->method( 'getMetadata' )
			->will( $this->returnValue( $exifData ) );

		$file->expects( $expectedToRun )
			->method( 'getWidth' )
			->will( $this->returnValue(
				isset( $parameters['getWidth'] ) ? $parameters['getWidth'] : false
		) );

		$file->expects( $expectedToRun )
			->method( 'getHeight' )
			->will( $this->returnValue(
				isset( $parameters['getHeight'] ) ? $parameters['getHeight'] : false
		) );

		$instance = new ExifDataAnnotator( $semanticData, $file );

		$this->assertTrue(
			$instance->addAnnotation()
		);

		return $semanticData;
	}

}
