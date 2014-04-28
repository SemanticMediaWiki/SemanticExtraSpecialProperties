<?php

namespace SESP\Tests\Integration;

use SESP\DIC\DIContainer;
use SESP\DIC\ObjectFactory;

use Title;

/**
 * @uses \SESP\DIC\DIContainer
 * @uses \SESP\DIC\ObjectFactory
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @license GNU GPL v2+
 * @since 1.3.0
 *
 * @author mwjames
 */
class ObjectFactoryIntegrationTest extends \PHPUnit_Framework_TestCase {

	protected $objectFactory;

	protected function setUp() {
		parent::setUp();

		$this->objectFactory = ObjectFactory::getInstance();
		$this->objectFactory->registerContainer( new DIContainer() );
	}

	protected function tearDown() {
		$this->objectFactory->clear();

		parent::tearDown();
	}

	public function testConstructWikiPage() {

		$this->assertInstanceOf(
			'\WikiPage',
			$this->objectFactory->newWikiPage( Title::newFromText( __METHOD__ ) )
		);
	}

	public function testConstructDBConnection() {

		$this->assertInstanceOf(
			'\DatabaseBase',
			$this->objectFactory->getDBConnection( DB_SLAVE )
		);
	}

	public function testConstructUserFromName() {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getText' )
			->will( $this->returnValue( __METHOD__ ) );

		$this->assertInstanceOf(
			'\User',
			$this->objectFactory->newUserFromName( $title )
		);
	}

	public function testConstructUserFromId() {

		$this->assertInstanceOf(
			'\User',
			$this->objectFactory->newUserFromId( 9999 )
		);
	}

}
