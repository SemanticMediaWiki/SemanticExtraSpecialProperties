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
	}

	protected function prepSelect( \Title $title = null, $calls ) {
		$this->connection = $this->getMockBuilder( '\DatabaseBase' )
			->disableOriginalConstructor()
			->getMock();

		$titleKey = $title === null ? null : $title->getDBkey();

		$this->appFactory->setConnection( $this->connection );
		return $this->connection->expects( $calls )
			->method( "select" )
			->with( [ 'logging', 'user' ],
					[
						'log_id', 'log_type', 'log_action', 'log_timestamp',
						'log_user', 'log_user_text', 'log_namespace',
						'log_title', 'log_params', 'log_deleted', 'user_id',
						'user_name', 'user_editcount',
						'log_comment_text' => 'log_comment',
						'log_comment_data' => 'NULL',
						'log_comment_cid' => 'NULL',
					], [ 'log_type' => 'approval', 'log_title' => $titleKey ],
					'SESP\DatabaseLogReader::getLog',
					[ 'ORDER BY' => 'log_timestamp desc' ],
					[ 'user' => [ 'LEFT JOIN', 'log_user=user_id' ] ] );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			DatabaseLogReader::class,
			$this->appFactory->newDatabaseLogReader()
		);
	}

	public function testGetQuery() {
		$log = $this->appFactory->newDatabaseLogReader();
		$this->assertEquals( null, $log->getQuery() );
	}

	public function testNoLog() {
		$log = $this->appFactory->newDatabaseLogReader();
		$this->assertEquals( null, $log->getUserForLogEntry() );
	}

	public function testGetNull() {
		$title = \Title::newFromText( "none" );
		$this->prepSelect( $title, $this->once() )->will( $this->returnValue( null ) );
		$log = $this->appFactory->newDatabaseLogReader( $title );
		$this->assertEquals( null, $log->getUserForLogEntry() );
		$this->assertEquals( null, $log->getDateOfLogEntry() );
		$this->assertEquals( null, $log->getStatusOfLogEntry() );

		$query = $log->getQuery();
		$this->assertEquals( [ 'tables', 'fields', 'conds', 'options', 'join_conds' ],
							 array_keys( $query ) );
	}

	public function testGetLogAndQuery() {
		$this->prepSelect( \Title::newMainPage(), $this->once() )->will( $this->returnValue(
			new \ArrayIterator( [ (object)[
					'user_id' => 1,
					'log_timestamp' => 5,
					'log_action' => 'bloop'
				] ] ) ) );
		$log = $this->appFactory->newDatabaseLogReader( \Title::newMainPage() );
		$this->assertEquals( \User::newFromID( 1 ), $log->getUserForLogEntry() );
		$this->assertEquals( new \MWTimestamp( 5 ), $log->getDateOfLogEntry() );
		$this->assertEquals( "bloop", $log->getStatusOfLogEntry() );

		$query = $log->getQuery();
		$this->assertEquals( [ 'tables', 'fields', 'conds', 'options', 'join_conds' ],
							 array_keys( $query ) );
	}

	public function testCache() {
		$this->prepSelect( \Title::newMainPage(), $this->never() )->will( $this->returnValue(
			new \ArrayIterator( [ (object)[
					'user_id' => 3,
					'log_timestamp' => 10,
					'log_action' => 'beep'
				] ] ) ) );
		$log = $this->appFactory->newDatabaseLogReader( \Title::newMainPage() );
		$this->assertEquals( \User::newFromID( 1 ), $log->getUserForLogEntry() );
		$this->assertEquals( new \MWTimestamp( 5 ), $log->getDateOfLogEntry() );
		$this->assertEquals( "bloop", $log->getStatusOfLogEntry() );
	}

	public function testClearCache() {
		$this->prepSelect( \Title::newMainPage(), $this->once() )->will( $this->returnValue(
			new \ArrayIterator( [ (object)[
					'user_id' => 4,
					'log_timestamp' => 50,
					'log_action' => 'ding'
				] ] ) ) );
		$log = $this->appFactory->newDatabaseLogReader( \Title::newMainPage() );
		$log->clearCache();
		$this->assertEquals( \User::newFromID( 4 ), $log->getUserForLogEntry() );
		$this->assertEquals( new \MWTimestamp( 50 ), $log->getDateOfLogEntry() );
		$this->assertEquals( "ding", $log->getStatusOfLogEntry() );

		$query = $log->getQuery();
		$this->assertEquals( [ 'tables', 'fields', 'conds', 'options', 'join_conds' ],
							 array_keys( $query ) );
	}
}
