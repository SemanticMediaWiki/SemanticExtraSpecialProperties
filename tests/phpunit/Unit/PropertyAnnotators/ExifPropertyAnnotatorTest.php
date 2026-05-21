<?php

namespace SESP\Tests\PropertyAnnotators;

use File;
use MediaWiki\Title\Title;
use SESP\AppFactory;
use SESP\LabelFetcher;
use SESP\PropertyAnnotators\ExifPropertyAnnotator;
use SESP\PropertyDefinitions;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;
use WikiFilePage;

/**
 * @covers \SESP\PropertyAnnotators\ExifPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ExifPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___EXIFDATA' );
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
		$file = $this->getMockBuilder( File::class )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->willReturn( false );

		$wikiPage = $this->getMockBuilder( WikiFilePage::class )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getFile' )
			->willReturn( $file );

		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'inNamespace' )
			->willReturn( true );

		$subject = $this->getMockBuilder( WikiPage::class )
			->disableOriginalConstructor()
			->getMock();

		$subject->expects( $this->once() )
			->method( 'getTitle' )
			->willReturn( $title );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->willReturn( $wikiPage );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$instance = new ExifPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	/**
	 * @dataProvider metaProvider
	 */
	public function testAddAnnotation( $meta, $defs, $expected ) {
		$labelFetcher = $this->getMockBuilder( LabelFetcher::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyDefinitions = new PropertyDefinitions(
			$labelFetcher
		);

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$subject = WikiPage::newFromText( __METHOD__, NS_FILE );

		$file = $this->getMockBuilder( File::class )
			->disableOriginalConstructor()
			->getMock();

		$file->expects( $this->once() )
			->method( 'exists' )
			->willReturn( true );

		$file->expects( $this->once() )
			->method( 'getMetadata' )
			->willReturn( serialize( $meta ) );

		$wikiPage = $this->getMockBuilder( WikiFilePage::class )
			->disableOriginalConstructor()
			->getMock();

		$wikiPage->expects( $this->once() )
			->method( 'getFile' )
			->willReturn( $file );

		$this->appFactory->expects( $this->once() )
			->method( 'newWikiPage' )
			->willReturn( $wikiPage );

		$this->appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->willReturn( $propertyDefinitions );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

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
