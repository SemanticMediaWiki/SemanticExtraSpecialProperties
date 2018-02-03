<?php

namespace SESP\Tests;

use SESP\DatabaseLogReader;
use SESP\AppFactory;

/**
 * @covers \SESP\DatabaseLogReader
 * @group semantic-extra-special-properties
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class DatabaseLogReaderTest extends \PHPUnit_Framework_TestCase {

	private $appFactory;
	private $connection;

	protected function setUp() {
		parent::setUp();

		$this->appFactory = new AppFactory;

		$this->connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			DatabaseLogReader::class,
			$this->appFactory->newDatabaseLogReader()
		);
	}

	public function testGetQuery() {
		$log = $this->appFactory->newDatabaseLogReader();

		$this->assertNull(
			$log->getQuery()
		);
	}

	public function testNoLog() {
		$log = $this->appFactory->newDatabaseLogReader();

		$this->assertNull(
			$log->getUserForLogEntry()
		);
	}

	public function testGetNull() {
		$title = \Title::newFromText( "none" );

		$log = $this->appFactory->newDatabaseLogReader( $title );

		$this->assertNull(
			$log->getUserForLogEntry()
		);

		$this->assertNull(
			$log->getDateOfLogEntry()
		);

		$this->assertNull(
			$log->getStatusOfLogEntry()
		);

		$query = $log->getQuery();

		$this->assertEquals(
			[ 'tables', 'fields', 'conds', 'options', 'join_conds' ],
			array_keys( $query )
		);
	}

	public function testGetLogAndQuery() {
		$title = \Title::newFromText( __METHOD__ );

		$row = new \stdClass;
		$row->user_id = 1;
		$row->log_timestamp = 5;
		$row->log_action = 'bloop';

		$this->connection->expects( $this->any() )
			->method( 'select' )
			->will( $this->returnValue( new \ArrayIterator( [ $row ] ) ) );

		$this->appFactory->setConnection(
			$this->connection
		);

		$log = $this->appFactory->newDatabaseLogReader( $title );

		$this->assertEquals(
			\User::newFromID( 1 ),
			$log->getUserForLogEntry()
		);

		$this->assertEquals(
			new \MWTimestamp( 5 ),
			$log->getDateOfLogEntry()
		);

		$this->assertEquals(
			'bloop',
			$log->getStatusOfLogEntry()
		);

		$query = $log->getQuery();

		$this->assertEquals(
			[ 'tables', 'fields', 'conds', 'options', 'join_conds' ],
			array_keys( $query )
		);
	}

	public function testCache() {
		$title = \Title::newFromText( __METHOD__ );

		$row = new \stdClass;
		$row->user_id = 1;
		$row->log_timestamp = 5;
		$row->log_action = 'bloop';

		$this->connection->expects( $this->once() )
			->method( 'select' )
			->will( $this->returnValue( new \ArrayIterator( [ $row ] ) ) );

		$this->appFactory->setConnection(
			$this->connection
		);

		$log = $this->appFactory->newDatabaseLogReader( $title );
		$log->clearCache();

		$this->assertEquals(
			\User::newFromID( 1 ),
			$log->getUserForLogEntry()
		);

		// Second call on same title instance should be made from cache
		$this->assertEquals(
			\User::newFromID( 1 ),
			$log->getUserForLogEntry()
		);
	}

}
