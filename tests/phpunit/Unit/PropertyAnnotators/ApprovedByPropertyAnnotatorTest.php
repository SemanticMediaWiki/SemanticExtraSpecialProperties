<?php

namespace SESP\Tests\PropertyAnnotators;

use MediaWiki\Title\Title;
use MediaWiki\User\User;
use MediaWiki\User\UserIdentityValue;
use SESP\AppFactory;
use SESP\PropertyAnnotators\ApprovedByPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SESP\PropertyAnnotators\ApprovedByPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ApprovedByPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___APPROVEDBY' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ApprovedByPropertyAnnotator::class,
			new ApprovedByPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$annotator->isAnnotatorFor( $this->property )
		);
	}

	public function testAddAnnotation() {
		$user = User::newFromName( "UnitTest" );
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				WikiPage::newFromTitle( $user->getUserPage() ) );
		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedBy( $user );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	/**
	 * ApprovedRevs declares the approver as a UserIdentity, which is not necessarily a User.
	 */
	public function testAddAnnotationForApproverThatIsOnlyAUserIdentity() {
		$approver = new UserIdentityValue( 42, 'UnitTestIdentity' );

		$this->assertNotInstanceOf( User::class, $approver );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' )
			->with(
				$this->property,
				WikiPage::newFromTitle( Title::makeTitle( NS_USER, 'UnitTestIdentity' ) )
			);

		$semanticData->expects( $this->never() )
			->method( 'removeProperty' );

		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedBy( $approver );

		$annotator->addAnnotation( $this->property, $semanticData );
	}

	public function testRemoval() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'removeProperty' )
			->with( $this->property );

		$annotator = new ApprovedByPropertyAnnotator(
			$this->appFactory
		);

		$annotator->setApprovedBy( false );

		$annotator->addAnnotation( $this->property, $semanticData );
	}
}
