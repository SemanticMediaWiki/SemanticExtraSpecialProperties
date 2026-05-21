<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataItems\Blob;
/**
 * @covers \SESP\PropertyAnnotators\NamespacePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 */
class NamespaceNamePropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___NSNAME' );
	}

	/**
	 * @covers \SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator
	 */
	public function testCanConstruct() {
		$this->assertInstanceOf(
			NamespaceNamePropertyAnnotator::class,
			new NamespaceNamePropertyAnnotator( $this->appFactory )
		);
	}

	/**
	 * @covers \SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator::isAnnotatorFor
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
	 * @covers \SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator::addAnnotation
	 */
	public function testAddAnnotation( $nsid, $nsname ) {
		$subject = WikiPage::newFromText( __METHOD__, $nsid );

		$semanticData = $this->getMockBuilder( '\SMW\DataModel\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				new Blob( $nsname ) );
		$annotator = new NamespaceNamePropertyAnnotator(
			$this->appFactory
		);

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function nsProvider() {
		yield [ NS_USER, 'User' ];
		yield [ NS_MAIN, '(Main)' ];
	}
}
