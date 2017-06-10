<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\DispatchingPropertyAnnotator;
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
use SMW\DIProperty;
use SMW\DIWikiPage;

/**
 * @covers \SESP\PropertyAnnotators\DispatchingPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class DispatchingPropertyAnnotatorTest extends \PHPUnit_Framework_TestCase {

	private $property;
	private $appFactory;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( '\SESP\AppFactory' )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( 'Foo' );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			DispatchingPropertyAnnotator::class,
			new DispatchingPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {

		$instance = new DispatchingPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$propertyAnnotator = $this->getMockBuilder( '\SESP\PropertyAnnotator' )
			->disableOriginalConstructor()
			->getMock();

		$propertyAnnotator->expects( $this->once() )
			->method( 'addAnnotation' );

		$instance = new DispatchingPropertyAnnotator(
			$this->appFactory
		);

		$instance->addPropertyAnnotator( 'Foo', $propertyAnnotator );

		$instance->addAnnotation( new DIProperty( 'Foo' ), $semanticData );
	}

	/**
	 * @dataProvider propertyAnnotatorProvider
	 */
	public function testFindPropertyAnnotator( $property, $expected ) {

		$instance = new DispatchingPropertyAnnotator(
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
