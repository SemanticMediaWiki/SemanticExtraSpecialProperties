<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\PageImagesPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use Title;

/**
 * @covers \SESP\PropertyAnnotators\PageImagesPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageImagesPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

    private $property;
    private $appFactory;

    protected function setUp(): void {
        parent::setUp();

        $this->appFactory = $this->getMockBuilder( AppFactory::class )
            ->disableOriginalConstructor()
            ->getMock();

        $this->property = new DIProperty( '___PAGEIMG' );
    }

    public function testCanConstruct() {
        $this->assertInstanceOf(
            PageImagesPropertyAnnotator::class,
            new PageImagesPropertyAnnotator( $this->appFactory )
        );
    }

    public function testIsAnnotatorFor() {
        $instance = new PageImagesPropertyAnnotator(
            $this->appFactory
        );

        $this->assertTrue(
            $instance->isAnnotatorFor( $this->property )
        );
    }

    public function testAddAnnotation() {
        $title = $this->getMockBuilder( Title::class )
            ->disableOriginalConstructor()
            ->getMock();

        $subject = $this->getMockBuilder( DIWikiPage::class )
            ->disableOriginalConstructor()
            ->getMock();

        $subject->expects( $this->once() )
            ->method( 'getTitle' )
            ->will( $this->returnValue( $title ) );

        $semanticData = $this->getMockBuilder( SemanticData::class )
            ->disableOriginalConstructor()
            ->getMock();

        $semanticData->expects( $this->once() )
            ->method( 'getSubject' )
            ->will( $this->returnValue( $subject ) );

        $instance = $this->getMockBuilder( PageImagesPropertyAnnotator::class )
            ->disableOriginalConstructor()
            ->setMethods( [ 'getPageImageTitle' ] )
            ->getMock();

        $instance->expects( $this->once() )
            ->method( 'getPageImageTitle' )
            ->will( $this->returnValue( DIWikiPage::newFromText( __METHOD__ )->getTitle() ) );

        $instance->addAnnotation( $this->property, $semanticData );
    }

}
