<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\PropertyAnnotators\ApprovedByPropertyAnnotator;
use SESP\PropertyAnnotators\ApprovedDatePropertyAnnotator;
use SESP\PropertyAnnotators\ApprovedRevPropertyAnnotator;
use SESP\PropertyAnnotators\ApprovedStatusPropertyAnnotator;
use SESP\PropertyAnnotators\CreatorPropertyAnnotator;
use SESP\PropertyAnnotators\DispatchingPropertyAnnotator;
use SESP\PropertyAnnotators\ExifPropertyAnnotator;
use SESP\PropertyAnnotators\NamespaceNamePropertyAnnotator;
use SESP\PropertyAnnotators\NamespacePropertyAnnotator;
use SESP\PropertyAnnotators\NullPropertyAnnotator;
use SESP\PropertyAnnotators\PageContributorsPropertyAnnotator;
use SESP\PropertyAnnotators\PageIDPropertyAnnotator;
use SESP\PropertyAnnotators\PageImagesPropertyAnnotator;
use SESP\PropertyAnnotators\PageLengthPropertyAnnotator;
use SESP\PropertyAnnotators\PageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\PageViewsPropertyAnnotator;
use SESP\PropertyAnnotators\RevisionIDPropertyAnnotator;
use SESP\PropertyAnnotators\ShortUrlPropertyAnnotator;
use SESP\PropertyAnnotators\SubPagePropertyAnnotator;
use SESP\PropertyAnnotators\TalkPageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\UserBlockPropertyAnnotator;
use SESP\PropertyAnnotators\UserEditCountPerNsPropertyAnnotator;
use SESP\PropertyAnnotators\UserEditCountPropertyAnnotator;
use SESP\PropertyAnnotators\UserGroupPropertyAnnotator;
use SESP\PropertyAnnotators\UserRegistrationDatePropertyAnnotator;
use SESP\PropertyAnnotators\UserRightPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
/**
 * @covers \SESP\PropertyAnnotators\DispatchingPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class DispatchingPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( 'Foo' );
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
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyAnnotator = $this->getMockBuilder( PropertyAnnotator::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyAnnotator->expects( $this->once() )
			->method( 'addAnnotation' );

		$instance = new DispatchingPropertyAnnotator(
			$this->appFactory
		);

		$instance->addPropertyAnnotator( 'Foo', $propertyAnnotator );

		$instance->addAnnotation( new Property( 'Foo' ), $semanticData );
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
			$instance->findPropertyAnnotator( new Property( $property ) )
		);
	}

	public function propertyAnnotatorProvider() {
		$provider[] = [
			CreatorPropertyAnnotator::PROP_ID,
			CreatorPropertyAnnotator::class
		];

		$provider[] = [
			PageViewsPropertyAnnotator::PROP_ID,
			PageViewsPropertyAnnotator::class
		];

		$provider[] = [
			NamespacePropertyAnnotator::PROP_ID,
			NamespacePropertyAnnotator::class
		];

		$provider[] = [
			NamespaceNamePropertyAnnotator::PROP_ID,
			NamespaceNamePropertyAnnotator::class
		];

		$provider[] = [
			ApprovedRevPropertyAnnotator::PROP_ID,
			ApprovedRevPropertyAnnotator::class
		];

		$provider[] = [
			ApprovedByPropertyAnnotator::PROP_ID,
			ApprovedByPropertyAnnotator::class
		];

		$provider[] = [
			ApprovedDatePropertyAnnotator::PROP_ID,
			ApprovedDatePropertyAnnotator::class
		];

		$provider[] = [
			ApprovedStatusPropertyAnnotator::PROP_ID,
			ApprovedStatusPropertyAnnotator::class
		];

		$provider[] = [
			UserRegistrationDatePropertyAnnotator::PROP_ID,
			UserRegistrationDatePropertyAnnotator::class
		];

		$provider[] = [
			UserEditCountPropertyAnnotator::PROP_ID,
			UserEditCountPropertyAnnotator::class
		];

		$provider[] = [
			UserEditCountPerNsPropertyAnnotator::PROP_ID,
			UserEditCountPerNsPropertyAnnotator::class
		];

		$provider[] = [
			UserBlockPropertyAnnotator::PROP_ID,
			UserBlockPropertyAnnotator::class
		];

		$provider[] = [
			UserRightPropertyAnnotator::PROP_ID,
			UserRightPropertyAnnotator::class
		];

		$provider[] = [
			UserGroupPropertyAnnotator::PROP_ID,
			UserGroupPropertyAnnotator::class
		];

		$provider[] = [
			PageIDPropertyAnnotator::PROP_ID,
			PageIDPropertyAnnotator::class
		];

		$provider[] = [
			PageLengthPropertyAnnotator::PROP_ID,
			PageLengthPropertyAnnotator::class
		];

		$provider[] = [
			RevisionIDPropertyAnnotator::PROP_ID,
			RevisionIDPropertyAnnotator::class
		];

		$provider[] = [
			PageNumRevisionPropertyAnnotator::PROP_ID,
			PageNumRevisionPropertyAnnotator::class
		];

		$provider[] = [
			TalkPageNumRevisionPropertyAnnotator::PROP_ID,
			TalkPageNumRevisionPropertyAnnotator::class
		];

		$provider[] = [
			PageContributorsPropertyAnnotator::PROP_ID,
			PageContributorsPropertyAnnotator::class
		];

		$provider[] = [
			SubPagePropertyAnnotator::PROP_ID,
			SubPagePropertyAnnotator::class
		];

		$provider[] = [
			ShortUrlPropertyAnnotator::PROP_ID,
			ShortUrlPropertyAnnotator::class
		];

		$provider[] = [
			ExifPropertyAnnotator::PROP_ID,
			ExifPropertyAnnotator::class
		];

		$provider[] = [
			PageImagesPropertyAnnotator::PROP_ID,
			PageImagesPropertyAnnotator::class
		];

		$provider[] = [
			'Foo',
			NullPropertyAnnotator::class
		];

		return $provider;
	}

}
