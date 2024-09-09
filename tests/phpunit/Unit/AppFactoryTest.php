<?php

namespace SESP\Tests;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SESP\AppFactory;
use SESP\PropertyDefinitions;
use Title;
use User;
use WikiFilePage;
use Wikimedia\Rdbms\Database;
use WikiPage;

/**
 * @covers \SESP\AppFactory
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class AppFactoryTest extends \PHPUnit\Framework\TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			AppFactory::class,
			new AppFactory()
		);
	}

	public function testCanConstructWikiPage() {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->any() )
			->method( 'canExist' )
			->willReturn( true );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			WikiPage::class,
			$instance->newWikiPage( $title )
		);
	}

	public function testCanConstructWikiPageFrom_NS_MEDIA() {
		$title = Title::newFromText( 'Foo', NS_MEDIA );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			WikiFilePage::class,
			$instance->newWikiPage( $title )
		);
	}

	public function testCanConstructUserFromTitle() {
		$title = $this->getMockBuilder( Title::class )
			->disableOriginalConstructor()
			->getMock();

		$title->expects( $this->once() )
			->method( 'getText' )
			->willReturn( 'Foo' );

		$instance = new AppFactory();

		$this->assertInstanceOf(
			User::class,
			$instance->newUserFromTitle( $title )
		);
	}

	public function testCanConstructUserFromID() {
		$instance = new AppFactory();

		$this->assertInstanceOf(
			User::class,
			$instance->newUserFromID( 42 )
		);
	}

	public function testGetConnection() {
		$connection = $this->getMockBuilder( Database::class )
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

	public function testGetPropertyDefinitions() {
		$options = [
			'sespgDefinitionsFile' => '',
			'sespgLocalDefinitions' => []
		];

		$instance = new AppFactory(
			$options
		);

		$propertyDefinitions = $instance->getPropertyDefinitions();

		$this->assertInstanceOf(
			PropertyDefinitions::class,
			$propertyDefinitions
		);

		$this->assertSame(
			$propertyDefinitions,
			$instance->getPropertyDefinitions()
		);
	}

	public function testGetLogger() {
		$instance = new AppFactory();

		$this->assertInstanceOf(
			NullLogger::class,
			$instance->getLogger()
		);

		$logger = $this->getMockBuilder( LoggerInterface::class )
			->disableOriginalConstructor()
			->getMock();

		$instance->setLogger( $logger );

		$this->assertSame(
			$logger,
			$instance->getLogger()
		);
	}

	public function testGetOption() {
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
		$connection = $this->getMockBuilder( Database::class )
			->disableOriginalConstructor()
			->getMock();

		$appFactory = new AppFactory();
		$appFactory->setConnection( $connection );

		$dbLogReader = $appFactory->newDatabaseLogReader( null );
		$this->assertNull( $dbLogReader->getStatusOfLogEntry() );
	}
}
