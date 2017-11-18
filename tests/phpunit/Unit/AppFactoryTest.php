<?php

namespace SESP\Tests;

use SESP\AppFactory;

/**
 * @covers \SESP\AppFactory
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class AppFactoryTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			AppFactory::class,
			new AppFactory()
		);
	}

	public function testCanConstructWikiPage( ) {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\WikiPage',
			$instance->newWikiPage( $title )
		);
	}

	public function testCanConstructWikiPageFrom_NS_MEDIA() {

		$title = \Title::newFromText( 'Foo', NS_MEDIA );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\WikiFilePage',
			$instance->newWikiPage( $title )
		);
	}

	public function testCanConstructUserFromTitle( ) {

		$title = $this->getMockBuilder( '\Title' )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getText' )
			->will( $this->returnValue( 'Foo' ) );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\User',
			$instance->newUserFromTitle( $title )
		);
	}

	public function testCanConstructUserFromID( ) {

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\User',
			$instance->newUserFromID( 42 )
		);
	}

	public function testGetConnection( ) {

		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new AppFactory();

		$instance->setConnection(
			$connection
		);

		$this->assertSame(
			$connection,
			$instance->getConnection()
		);
	}

	public function testGetPropertyDefinitions( ) {

		$options = [
			'sespgDefinitionsFile' => '',
			'sespgLocalDefinitions' => []
		];

		$instance = new AppFactory(
			$options
		);

		$propertyDefinitions = $instance->getPropertyDefinitions();

		$this->assertInstanceOf(
			'\SESP\PropertyDefinitions',
			$propertyDefinitions
		);

		$this->assertSame(
			$propertyDefinitions,
			$instance->getPropertyDefinitions()
		);
	}

	public function testGetLogger( ) {

		$instance = new AppFactory();

		$this->assertInstanceOf(
			'\Psr\Log\NullLogger',
			$instance->getLogger()
		);

		$logger = $this->getMockBuilder( '\Psr\Log\LoggerInterface' )
			->disableOriginalConstructor()
			->getMock();

		$instance->setLogger( $logger );

		$this->assertSame(
			$logger,
			$instance->getLogger()
		);
	}

	public function testGetOption( ) {

		$options = [
			'Foo' => 'Bar'
		];

		$instance = new AppFactory(
			$options
		);

		$this->assertSame(
			'Bar',
			$instance->getOption( 'Foo' )
		);
	}

	public function testNewDatabaseLogReader() {
		$connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->getMock();

		$appFactory = new AppFactory();
		$appFactory->setConnection( $connection );

		$dbLogReader = $appFactory->newDatabaseLogReader( null );
		$dbLogReader->getStatusOfLogEntry();
	}
}
