<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\NamespacePropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDIString;
use User;

/**
 * @covers \SESP\PropertyAnnotators\NamespacePropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class NamespacePropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___NAMESPACE' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			NamespacePropertyAnnotator::class,
			new NamespacePropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$annotator = new NamespacePropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$userPage = User::newFromName( "UnitTest" )->getUserPage();
        $subject = $this->getMockBuilder( DIWikiPage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subject->expects( $this->once() )
            ->method( 'getTitle' )
            ->will( $this->returnValue( $userPage ) );

        $semanticData = $this->getMockBuilder( SemanticData::class )
            ->disableOriginalConstructor()
            ->getMock();

        $semanticData->expects( $this->once() )
            ->method( 'getSubject' )
            ->will($this->returnValue( $subject ));

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->equalTo( $this->property ),
				$this->equalTo( $userPage->getNsText() ) );

		$annotator = new NamespacePropertyAnnotator(
			$this->appFactory
		);

		$annotator->setNamespace( null );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->equalTo( $this->property ) );

		$annotator = new NamespacePropertyAnnotator(
			$this->appFactory
		);

		$annotator->setNamespace( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
