<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\UserEditCountPerNsPropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDIContainer;
use User;
use Wikimedia\Rdbms\Database;
use Wikimedia\Rdbms\FakeResultWrapper;

/**
 * @covers \SESP\PropertyAnnotators\UserEditCountPerNsPropertyAnnotator
 * @group semantic-extra-special-properties
 * @group Databasa
 *
 * @license GPL-2.0-or-later
 *
 * @author Alexander Mashin
 */
class UserEditCountPerNsPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	/** @var DIProperty */
	private $property;
	/** @var AppFactory */
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new DIProperty( '___USEREDITCNTNS' );
	}

	public function testIsAnnotatorFor() {
		$instance = new UserEditCountPerNsPropertyAnnotator( $this->appFactory );
		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	/**
	 * @dataProvider getEditsPerNsProvider
	 * @param int $id User ID.
	 * @param string $ip Anonymous user's IP address.
	 * @param array $expected Expected result.
	 */
	public function testGetEditsPerNs( $id, $ip, array $expected ) {
		// Mock the database.
		$db = $this->getMockBuilder( Database::class )
			->disableOriginalConstructor()
			->getMock();
		$fake = [];
		foreach ( $expected as $ns => $edits ) {
			$fake[] = [ 'ns' => $ns, 'edits' => $edits ];
		}
		$db->method( 'select' )
			->willReturn( new FakeResultWrapper( $fake ) );

		// Mock the application factory (only for the mock database).
		$factory = $this->appFactory;
		$factory->method( 'getConnection' )
			->willReturn( $db );

		$annotator = new UserEditCountPerNsPropertyAnnotator( $factory );
		// Expose the private method getEditsPerNs().
		$reflector = new \ReflectionObject( $annotator );
		$method = $reflector->getMethod( 'getEditsPerNs' );
		$method->setAccessible( true );

		// Call the tested method.
		$stats = $method->invokeArgs( $annotator, [ $id, $ip ] );

		$this->assertEquals( $expected, $stats );
	}

	/**
	 * @return array
	 */
	public function getEditsPerNsProvider(): array {
		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();
		$id = $user->getId();

		$provider = [];

		// Registered user.
		$provider[] = [ $id, '', [ 0 => 42, 1 => 21 ] ];
		// Anonymous user.
		$provider[] = [ 0, '127.0.0.1', [ 0 => 84, 1 => 42 ] ];

		return $provider;
	}

	/**
	 * @dataProvider containerProvider
	 * @param int $ns Namespace
	 * @param int $edits Number of edits
	 */
	public function testContainer( $ns, $edits ) {
		$subject = new DIWikiPage( 'Test', NS_USER );

		$annotator = new UserEditCountPerNsPropertyAnnotator( $this->appFactory );
		// Expose the private method container().
		$reflector = new \ReflectionObject( $annotator );
		$method = $reflector->getMethod( 'container' );
		$method->setAccessible( true );
		$container = $method->invokeArgs( $annotator, [ $subject, $ns, $edits ] );

		$this->assertInstanceOf( SMWDIContainer::class, $container, 'Container is not an instance of SMWDIContainer' );

		$data = $container->getSemanticData();
		$properties = $data->getProperties();

		$this->assertArrayHasKey( '___USEREDITCNTNS_NS', $properties, 'No namespace number in the container' );
		$nsValue = $data->getPropertyValues( new DIProperty( '___USEREDITCNTNS_NS' ) )[0]->getNumber();
		$this->assertEquals( $ns, $nsValue, 'Wrong namespace number' );

		$this->assertArrayHasKey( '___USEREDITCNTNS_CNT', $properties, 'No edit cont in the container' );
		$editsValue = $data->getPropertyValues( new DIProperty( '___USEREDITCNTNS_CNT' ) )[0]->getNumber();
		$this->assertEquals( $edits, $editsValue, 'Wrong edit count' );
	}

	/**
	 * @return array
	 */
	public function containerProvider(): array {
		return [
			[ 0, 42 ],
			[ 1, 21 ]
		];
	}

	/**
	 * @dataProvider addAnnotationProvider
	 * @param int $namespace Subject page namespace
	 * @param string|null $name User name
	 * @param string|null $ip Anonymous iser's I address
	 * @param array $stats
	 */
	public function testAddAnnotation( $namespace, $name, $ip, array $stats ) {
		$subject = new DIWikiPage( $name ?: $ip, $namespace );
		$semanticData = new SemanticData( $subject );

		$total = array_sum( $stats );

		// Mock the database.
		$db = $this->getMockBuilder( Database::class )
			->disableOriginalConstructor()
			->getMock();
		$fake = [];
		foreach ( $stats as $ns => $edits ) {
			$fake[] = [ 'ns' => $ns, 'edits' => $edits ];
		}
		$db->method( 'select' )
			->willReturn( new FakeResultWrapper( $fake ), new FakeResultWrapper( $fake ) );

		// Mock the application factory; only for the database.
		$factory = $this->appFactory;
		$factory->method( 'getConnection' )
			->willReturn( $db );

		$user = $this->getMockBuilder( User::class )
			->disableOriginalConstructor()
			->getMock();
		$user->method( 'getId' )
			->willReturn( 1 );
		$user->expects( $namespace === NS_USER ? $this->atMost( 2 ) : $this->never() )
			->method( 'getEditCount' )
			->willReturn( $total );

		$factory = $this->appFactory;

		$this->appFactory->expects( $namespace === NS_USER ? $this->once() : $this->never() )
			->method( 'newUserFromTitle' )
			->willReturn( $user );

		$annotator = new UserEditCountPerNsPropertyAnnotator( $factory );

		$annotator_return = $annotator->addAnnotation( $this->property, $semanticData );
		// Always returns void.
		$this->assertNull(
			$annotator_return,
			'UserEditCountPerNsPropertyAnnotator::addAnnotation() should return nothing'
		);

		$properties = $semanticData->getProperties();

		if ( count( $stats ) > 0 && is_int( $total ) ) {
			$this->assertArrayHasKey( '___USEREDITCNTNS', $properties, 'No edit count record for the page' );

			$records = $semanticData->getPropertyValues( new DIProperty( '___USEREDITCNTNS' ) );
			foreach ( $records as $record ) {
				$this->assertInstanceOf( DIWikiPage::class, $record );
			}
			$actual = [];
			foreach ( $semanticData->getSubSemanticData() as $subSemanticDatum ) {
				$properties = $subSemanticDatum->getProperties();
				$this->assertArrayHasKey( '___USEREDITCNTNS_NS', $properties, 'No namespace number for the record' );
				$ns = $subSemanticDatum->getPropertyValues( $properties['___USEREDITCNTNS_NS'] )[0]->getNumber();
				$this->assertArrayHasKey( '___USEREDITCNTNS_CNT', $properties, 'No edit count for the record' );
				$edits = $subSemanticDatum->getPropertyValues( $properties['___USEREDITCNTNS_CNT'] )[0]->getNumber();
				$actual[$ns] = $edits;
			}
			$this->assertEquals( $stats, $actual, 'Edit count arrays mismatch' );
		} else {
			// No edits for this user or the total edits are somehow not integer.
			$this->assertArrayNotHasKey(
				'___USEREDITCNTNS',
				$properties,
				'Edit count record for the page where it should not be'
			);
		}
	}

	/**
	 * @return array
	 */
	public function addAnnotationProvider(): array {
		return [
			'Named user with edits' => [ NS_USER, 'Test user', null, [ 0 => 42, 1 => 2, 8 => 7 ] ],
			'Named user without edits' => [ NS_USER, 'Test user', null, [] ],
			'Named user with non-integer edits' => [ NS_USER, 'Test user', null, [ 0 => 0.5 ] ],
			'Anonymous user with edits' => [ NS_USER, null, '127.0.0.1', [ 0 => 84, 8 => 21 ] ],
			'Anonymous user without edits' => [	NS_USER, null, '127.0.0.1', [] ],
			'Anonymous user with non-integer edits' => [ NS_USER, 'Test user', null, [ 0 => 0.5 ] ],
			'Subject not in User: namespace' => [ NS_MAIN, 'Test page', null, [] ]
		];
	}
}
