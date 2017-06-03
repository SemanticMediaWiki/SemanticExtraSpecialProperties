<?php

namespace SESP\Tests;

use SESP\ExtraPropertyAnnotator;
use SESP\PropertyDefinitions;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SESP\PropertyAnnotators\NullPropertyAnnotator;
use SESP\PropertyAnnotators\CreatorPropertyAnnotator;
use SESP\PropertyAnnotators\PageViewsPropertyAnnotator;
use SESP\PropertyAnnotators\LocalPropertyAnnotator;
use SESP\PropertyAnnotators\UserRegistrationDatePropertyAnnotator;
use SESP\PropertyAnnotators\UserEditCountPropertyAnnotator;
use SESP\PropertyAnnotators\PageIDPropertyAnnotator;
use SESP\PropertyAnnotators\ShortUrlPropertyAnnotator;
use SESP\PropertyAnnotators\ExifPropertyAnnotator;
use SESP\PropertyAnnotators\RevisionIDPropertyAnnotator;
use SESP\PropertyAnnotators\PageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\TalkPageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\PageContributorsPropertyAnnotator;
use SESP\PropertyAnnotators\SubPagePropertyAnnotator;
use SESP\PropertyAnnotators\PageLengthPropertyAnnotator;

/**
 * @covers \SESP\ExtraPropertyAnnotator
 * @group SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ExtraPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ExtraPropertyAnnotator::class,
			new ExtraPropertyAnnotator( $this->appFactory )
		);
	}

	public function testaddAnnotationOnInvalidSubject() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$instance = new ExtraPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $semanticData );
	}

	public function testAddAnnotationOnLocalDef() {

		$appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->setMethods( array( 'getPropertyDefinitions', 'getOption' ) )
			->getMock();

		$subject = DIWikiPage::newFromText( __METHOD__ );

		$callback = function( $appFactory, $property, $semanticData ) {
			return $semanticData->getSubject();
		};

		$localPropertyDefinitions['FAKE_PROP'] = array(
			'id'    => 'FAKE_PROP',
			'callback' => $callback
		);


		$propertyDefinitions = new PropertyDefinitions();

		$propertyDefinitions->setLocalPropertyDefinitions(
			$localPropertyDefinitions
		);

		$appFactory->expects( $this->at( 0 ) )
			->method( 'getPropertyDefinitions' )
			->will( $this->returnValue( $propertyDefinitions ) );

		$appFactory->expects( $this->at( 1 ) )
			->method( 'getOption' )
			->with( $this->equalTo( 'sespSpecialProperties' ) )
			->will( $this->returnValue( $localPropertyDefinitions ) );

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->will( $this->returnValue( $subject ) );

		$instance = new ExtraPropertyAnnotator(
			$appFactory
		);

		$instance->addAnnotation( $semanticData );
	}

	/**
	 * @dataProvider propertyAnnotatorProvider
	 */
	public function testFindPropertyAnnotator( $property, $expected ) {

		$instance = new ExtraPropertyAnnotator(
			$this->appFactory
		);

		$this->assertInstanceOf(
			$expected,
			$instance->findPropertyAnnotator( new DIProperty( $property ) )
		);
	}

	public function propertyAnnotatorProvider() {

		$provider[] = array(
			CreatorPropertyAnnotator::PROP_ID,
			CreatorPropertyAnnotator::class
		);

		$provider[] = array(
			PageViewsPropertyAnnotator::PROP_ID,
			PageViewsPropertyAnnotator::class
		);

		$provider[] = array(
			UserRegistrationDatePropertyAnnotator::PROP_ID,
			UserRegistrationDatePropertyAnnotator::class
		);

		$provider[] = array(
			UserEditCountPropertyAnnotator::PROP_ID,
			UserEditCountPropertyAnnotator::class
		);

		$provider[] = array(
			PageIDPropertyAnnotator::PROP_ID,
			PageIDPropertyAnnotator::class
		);

		$provider[] = array(
			PageLengthPropertyAnnotator::PROP_ID,
			PageLengthPropertyAnnotator::class
		);

		$provider[] = array(
			RevisionIDPropertyAnnotator::PROP_ID,
			RevisionIDPropertyAnnotator::class
		);

		$provider[] = array(
			PageNumRevisionPropertyAnnotator::PROP_ID,
			PageNumRevisionPropertyAnnotator::class
		);

		$provider[] = array(
			TalkPageNumRevisionPropertyAnnotator::PROP_ID,
			TalkPageNumRevisionPropertyAnnotator::class
		);

		$provider[] = array(
			PageContributorsPropertyAnnotator::PROP_ID,
			PageContributorsPropertyAnnotator::class
		);

		$provider[] = array(
			SubPagePropertyAnnotator::PROP_ID,
			SubPagePropertyAnnotator::class
		);

		$provider[] = array(
			ShortUrlPropertyAnnotator::PROP_ID,
			ShortUrlPropertyAnnotator::class
		);

		$provider[] = array(
			ExifPropertyAnnotator::PROP_ID,
			ExifPropertyAnnotator::class
		);

		$provider[] = array(
			'Foo',
			NullPropertyAnnotator::class
		);

		return $provider;
	}

}
