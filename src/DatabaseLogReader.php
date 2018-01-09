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
	private $query;
	private $log;
	private $dbr;
	private $titleKey;
	private $type;

	/**
	 * @param DatabaseaBase $dbr injected connection
	 * @param string $titleKey from page name
	 * @param string $type of log (default: approval)
	 */
	public function __construct( DatabaseBase $dbr, $titleKey, $type = 'approval' ) {
		$this->dbr = $dbr;
		$this->titleKey = $titleKey;
		$this->type = $type;
	}

	/**
	 * Take care of loading from the cache or filling the query.
	 */
	private function init() {
		if ( !$this->query ) {
			if ( !isset( self::$titleCache[ $this->titleKey ] ) ) {
				$this->query = DatabaseLogEntry::getSelectQueryData();

				$this->query['conds'] = [
					'log_type' => $this->type,
					'log_title' => $this->titleKey
				];
				$this->query['options'] = [ 'ORDER BY' => 'log_timestamp desc' ];
				self::$titleCache[ $this->titleKey ] = $this;
			} else {
				$cache = self::$titleCache[ $this->titleKey ];
				$this->query = $cache->getQuery();
				$this->log = $cache->getLog();
			}
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
			$this->log = $this->dbr->select(
				$this->query['tables'], $this->query['fields'], $this->query['conds'],
				__METHOD__, $this->query['options'], $this->query['join_conds']
			);
			if ( $this->log === null ) {
				$this->log = new ArrayIterator( [
					'user_id' => null,
					'log_timestamp' => null,
					'log_action' => null
				] );
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
		if ( $logLine ) {
			return User::newFromID( $logLine->user_id );
		}
	}

	/**
	 * @return Timestamp
	 */
	public function getDateOfLogEntry() {
		$this->init();
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return new MWTimestamp( $logLine->log_timestamp );
		}
	}

	/**
	 * @return string
	 */
	public function getStatusOfLogEntry() {
		$this->init();
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return $logLine->log_action;
		}
	}
}
