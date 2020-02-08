<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ShortUrlPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\ShortUrlPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ShortUrlPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___SHORTURL' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ShortUrlPropertyAnnotator::class,
			new ShortUrlPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new ShortUrlPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testMissingShortUrlUtilsThrowsException() {

		// PHPUnit 6.5+
		if ( is_callable( [ $this, 'expectException' ] ) ) {
			$this->expectException( '\RuntimeException' );
		} else {
			$this->setExpectedException( '\RuntimeException' );
		}

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$instance = $this->getMockBuilder( ShortUrlPropertyAnnotator::class )
			->disableOriginalConstructor()
			->setMethods( [ 'hasShortUrlUtils' ] )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->will( $this->returnValue( false ) );

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testAddAnnotation() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = $this->getMockBuilder( ShortUrlPropertyAnnotator::class )
			->disableOriginalConstructor()
			->setMethods( [ 'hasShortUrlUtils', 'getShortUrl' ] )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->will( $this->returnValue( true ) );

		$instance->expects( $this->once() )
			->method( 'getShortUrl' )
			->will( $this->returnValue( 'foo' ) );

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
