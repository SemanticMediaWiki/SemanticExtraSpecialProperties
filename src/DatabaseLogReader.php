<?php

namespace SESP;

use DatabaseLogEntry;
use MWTimestamp;
use Title;
use User;

class DatabaseLogReader {

	// The parameters for our query.
	protected $query;

	// The current log
	protected $log;

	// Don't run multiple queries if we don't have to
	protected static $titleCache = [];

	// The appFactory
	protected $appFactory;

	/**
	 * Constructor for reading the log.
	 *
	 * @param AppFactory $appFactory injected AppFactory
	 * @param Title $title page
	 * @param string $type of log (default: approval)
	 */
	public function __construct( AppFactory $appFactory, Title $title, $type = 'approval' ) {
		$this->appFactory = $appFactory;
		if ( !isset( self::$titleCache[ $title->getDBKey() ] ) ) {
			$this->query = DatabaseLogEntry::getSelectQueryData();

			$this->query['conds'] = [
				'log_type' => $type,
				'log_title' => $title->getDBKey()
			];
			$this->query['options'] = [ 'ORDER BY' => 'log_timestamp desc' ];
		} else {
			$cache = self::$titleCache[ $title->getDBKey() ];
			$this->query = $cache->getQuery();
			$this->log = $cache->getLog();
		}
	}

	/**
	 * Fetch the query for later calls
	 *
	 * @return array
	 */
	public function getQuery() {
		return $this->query;
	}

	/**
	 * Fetch the results using our conditions
	 *
	 * @return IResultWrapper
	 * @throws DBError
	 */
	protected function getLog() {
		if ( !$this->log ) {
			$this->log = $this->appFactory->getConnection()->select(
				$this->query['tables'], $this->query['fields'], $this->query['conds'],
				__METHOD__, $this->query['options'], $this->query['join_conds']
			);
		}
		return $this->log;
	}

	/**
	 * Get the person who made the last for this page
	 *
	 * @return User
	 */
	public function getUser() {
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return User::newFromID( $logLine->user_id );
		}
	}

	/**
	 * Get the date of the last entry in the log for this page
	 *
	 * @return Timestamp
	 */
	public function getDate() {
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return new MWTimestamp( $logLine->log_timestamp );
		}
	}

	/**
	 * Get the status of the last entry in the log for this page
	 *
	 * @return Timestamp
	 */
	public function getStatus() {
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return $logLine->log_action;
		}
	}
}
