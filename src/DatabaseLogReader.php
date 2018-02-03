<?php

namespace SESP;

use ArrayIterator;
use DatabaseLogEntry;
use DatabaseBase;
use MWTimestamp;
use Title;
use User;

class DatabaseLogReader {

	private static $titleCache = [];

	/**
	 * @var DatabaseBase
	 */
	private $dbr;

	/**
	 * @var string
	 */
	private $query;

	/**
	 * @var string
	 */
	private $log;

	/**
	 * @var string
	 */
	private $dbKey;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param DatabaseaBase $dbr injected connection
	 * @param string $dbKey from page name
	 * @param string $type of log (default: approval)
	 */
	public function __construct( DatabaseBase $dbr, Title $title = null , $type = 'approval' ) {
		$this->dbr = $dbr;
		$this->dbKey = $title instanceof Title ? $title->getDBkey() : null;
		$this->type = $type;
	}

    public function clearCache() {
        self::$titleCache = [];
    }

    /**
	 * Take care of loading from the cache or filling the query.
	 */
	private function init() {

		if ( $this->query ) {
			return;
		}

		if ( !isset( self::$titleCache[ $this->dbKey ] ) ) {
			$this->query = DatabaseLogEntry::getSelectQueryData();

			$this->query['conds'] = [
				'log_type' => $this->type,
				'log_title' => $this->dbKey
			];
			$this->query['options'] = [ 'ORDER BY' => 'log_timestamp desc' ];
			self::$titleCache[ $this->dbKey ] = $this;
		} else {
			$cache = self::$titleCache[ $this->dbKey ];
			$this->query = $cache->getQuery();
			$this->log = $cache->getLog();
		}

	}

	/**
	 * Fetch the results using our conditions
	 *
	 * @return IResultWrapper
	 * @throws DBError
	 */
	private function getLog() {
		if ( !$this->log ) {

            $query = $this->getQuery();

			$this->log = $this->dbr->select(
				$query['tables'],
				$query['fields'],
				$query['conds'],
				__METHOD__,
				$query['options'],
				$query['join_conds']
			);

			if ( $this->log === null ) {
				$this->log = new ArrayIterator( [ (object)[
					'user_id' => null,
					'log_timestamp' => null,
					'log_action' => null
				] ] );
			}
		}

		return $this->log;
	}

	/**
	 * Fetch the query parameters for later calls
	 *
	 * @return array of parameters for SELECT call
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * @return User
	 */
	public function getUserForLogEntry() {
		$this->init();
		$logLine = $this->getLog()->current();

		if ( $logLine && $logLine->user_id ) {
			return User::newFromID( $logLine->user_id );
		}
	}

	/**
	 * @return Timestamp
	 */
	public function getDateOfLogEntry() {
		$this->init();
		$logLine = $this->getLog()->current();

		if ( $logLine && $logLine->log_timestamp ) {
			return new MWTimestamp( $logLine->log_timestamp );
		}
	}

	/**
	 * @return string
	 */
	public function getStatusOfLogEntry() {
		$this->init();
		$logLine = $this->getLog()->current();

		if ( $logLine && $logLine->log_action ) {
			return $logLine->log_action;
		}
	}
}
