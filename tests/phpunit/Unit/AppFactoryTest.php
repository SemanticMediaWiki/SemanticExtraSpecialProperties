<?php

namespace SESP\Tests;

use SESP\AppFactory;

/**
 * @covers \SESP\AppFactory
 *
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 1.3
 *
 * @author mwjames
 */
class AppFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SESP\AppFactory',
			new AppFactory()
		);
	}

	public function testCanConstructDatabaseConnection( ) {

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\DatabaseBase',
			$instance->newDatabaseConnection()
		);
	}

	public function testCanConstructWikiPage( ) {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\WikiPage',
			$instance->newWikiPage( $title )
		);
	}

	public function testCanConstructUser( ) {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getText' )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\User',
			$instance->newUserFromTitle( $title )
		);
	}

	public function testCanConstructShortUrlAnnotator( ) {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\SESP\Annotator\ShortUrlAnnotator',
			$instance->newShortUrlAnnotator( $semanticData )
		);
	}

	public function testCanConstructExifDataAnnotator( ) {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( '\File' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\SESP\Annotator\ExifDataAnnotator',
			$instance->newExifDataAnnotator( $semanticData, $file )
		);
	}

}
