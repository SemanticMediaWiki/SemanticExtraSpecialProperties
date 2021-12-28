<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDIBlob;

/**
 * @covers \SESP\PropertyAnnotators\NamespacePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 */
class NamespaceNamePropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___NSNAME' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			NamespaceNamePropertyAnnotator::class,
			new NamespaceNamePropertyAnnotator( $this->appFactory )
		);
	}

	/**
	 * @covers \SESP\PropertyAnnotators\NamespacePropertyAnnotator::isAnnotatorFor
	 */
	public function testIsAnnotatorFor() {

		$annotator = new NamespaceNamePropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider nsProvider
	 * @covers \SESP\PropertyAnnotators\NamespacePropertyAnnotator::addAnnotation
	 */
	public function testAddAnnotation( $nsid, $nsname ) {
		$namespace = $nsid;
		$subject = DIWikiPage::newFromText( __METHOD__, $namespace );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->equalTo( $this->property ),
				$this->equalTo( new SMWDIBlob( $nsname ) ) );
		$annotator = new NamespaceNamePropertyAnnotator(
			$this->appFactory
		);

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function nsProvider() {
		yield [ NS_USER, 'User'];
		yield [ NS_MAIN, '(Main)' ];
	}
}
