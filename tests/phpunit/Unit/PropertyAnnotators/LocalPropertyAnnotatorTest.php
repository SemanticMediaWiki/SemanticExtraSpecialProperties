<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\LocalPropertyAnnotator;
use SMW\DIWikiPage;
use SMW\DIProperty;

/**
 * @covers \SESP\PropertyAnnotators\LocalPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class LocalPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $appFactory;
	private $property;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->setMethods( [ 'getOption' ] )
			->getMock();

		$this->property = new DIProperty( 'FAKE_PROP' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			LocalPropertyAnnotator::class,
			new LocalPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new LocalPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$callback = function( $appFactory, $property, $semanticData ) {
			return $semanticData->getSubject();
		};

		$localPropertyDefinitions = [];

		$localPropertyDefinitions['FAKE_PROP'] = [
			'id'    => 'FAKE_PROP',
			'callback' => $callback
		];

		$this->appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( $this->equalTo( 'sespgLocalDefinitions' ) )
			->will( $this->returnValue( $localPropertyDefinitions ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = new LocalPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testAddAnnotationOnInvalidLocalDef() {

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$localPropertyDefinitions = [];
		$localPropertyDefinitions['FAKE_PROP'] = [];

		$this->appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( $this->equalTo( 'sespgLocalDefinitions' ) )
			->will( $this->returnValue( $localPropertyDefinitions ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$instance = new LocalPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
