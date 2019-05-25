<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ExifPropertyAnnotator;
use SESP\PropertyDefinitions;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\ExifPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ExifPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___EXIFDATA' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ExifPropertyAnnotator::class,
			new ExifPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new ExifPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testTryAddAnnotationForNonExistingFile() {

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( false ) );

		$wikiPage = $this->getMockBuilder( '\WikiFilePage' )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getFile' )
			->will( $this->returnValue( $file ) );

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->will( $this->returnValue( true ) );

		$subject = $this->getMockBuilder( '\SMW\DIWikiPage' )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->will( $this->returnValue( $title ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$instance = new ExifPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	/**
	 * @dataProvider metaProvider
	 */
	public function testAddAnnotation( $meta, $defs, $expected ) {

		$labelFetcher = $this->getMockBuilder( '\SESP\LabelFetcher' )
			->disableOriginalConstructor()
			->getMock();

		$propertyDefinitions = new PropertyDefinitions(
			$labelFetcher
		);

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$subject = DIWikiPage::newFromText( __METHOD__, NS_FILE );

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->will( $this->returnValue( true ) );

		$file->expects( $this->once() )
			->method( 'getMetadata' )
			->will( $this->returnValue( serialize( $meta ) ) );

		$wikiPage = $this->getMockBuilder( '\WikiFilePage' )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getFile' )
			->will( $this->returnValue( $file ) );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->will( $this->returnValue( $wikiPage ) );

		$this->appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $expected )
			->method( 'addPropertyObjectValue' );

		$instance = new ExifPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function metaProvider() {

		$provider[] = [
			[ 'Software' => 'ABC' ],
			[ '_EXIF' => [ 'SOFTWARE' => [ 'id' => 'Foo', 'type' => '_txt' ] ] ],
			$this->once()
		];

		$provider[] = [
			[ 'DateTimeOriginal' => '2013:01:11 02:13:35' ],
			[ '_EXIF' => [ 'DATETIMEORIGINAL' => [ 'id' => 'Foo', 'type' => '_dat' ] ] ],
			$this->once()
		];

		$provider[] = [
			[ 'DateTimeOriginal' => '2013:01:11 02:13' ],
			[ '_EXIF' => [ 'DATETIMEORIGINAL' => [ 'id' => 'Foo', 'type' => '_dat' ] ] ],
			$this->once()
		];

		// #113
		$provider[] = [
			[ 'DateTimeOriginal' => '2015:07:24 10:07:88' ],
			[ '_EXIF' => [ 'DATETIMEORIGINAL' => [ 'id' => 'Foo', 'type' => '_dat' ] ] ],
			$this->never()
		];

		$provider[] = [
			[ 'DateTimeOriginal' => '2013:01:11' ],
			[ '_EXIF' => [ 'DATETIMEORIGINAL' => [ 'id' => 'Foo', 'type' => '_dat' ] ] ],
			$this->once()
		];

		$provider['invalid-time'] = [
			[
				'DateTimeOriginal'  => '0000:00:00 00:00:00',
				'DateTime'  => '    :  :     :  :  ' ],
			[ '_EXIF' => [
				'DATETIMEORIGINAL' => [ 'id' => 'Foo', 'type' => '_dat' ],
				'DATETIME' => [ 'id' => 'Bar', 'type' => '_dat' ]
			] ],
			$this->never()
		];

		$provider['unmatchable'] = [
			[ 'Foo' => 'ABC' ],
			[ '_EXIF' => [ 'Foo' => [ 'id' => 'Foo', 'type' => '_txt' ] ] ],
			$this->never()
		];

		return $provider;
	}

}
