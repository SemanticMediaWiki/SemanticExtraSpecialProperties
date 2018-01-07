<?php

namespace SESP;

use DatabaseLogEntry;
use DatabaseBase;
use MWTimestamp;
use Title;
use User;

class DatabaseLogReader {

	// The parameters for our query.
	private $query;

	// The current log
	private $log;

	// Don't run multiple queries if we don't have to
	private static $titleCache = [];

	// The db connection
	private $db;

	// The title key
	private $titlekey;

	// The type of query being performed
	private $type;

	/**
	 * Constructor for reading the log.
	 *
	 * @param AppFactory $appFactory injected AppFactory
	 * @param Title $title page
	 * @param string $type of log (default: approval)
	 */
	public function __construct( DatabaseBase $db, Title $title, $type = 'approval' ) {
		$this->db = $db;
		$this->titlekey = $title->getDBKey();
		$this->type = $type;
	}

	/**
	 * Take care of loading from the cache or filling the query.
	 */
	private function init() {
		if ( !$this->query ) {
			if ( !isset( self::$titleCache[ $this->titlekey ] ) ) {
				$this->query = DatabaseLogEntry::getSelectQueryData();

				$this->query['conds'] = [
					'log_type' => $this->type,
					'log_title' => $this->titlekey
				];
				$this->query['options'] = [ 'ORDER BY' => 'log_timestamp desc' ];
				self::$titleCache[ $this->titlekey ] = $this;
			} elseif ( $this->query ) {
				$cache = self::$titleCache[ $this->titlekey ];
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
			$this->log = $this->db->select(
				$this->query['tables'], $this->query['fields'], $this->query['conds'],
				__METHOD__, $this->query['options'], $this->query['join_conds']
			);
		}
		return $this->log;
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
	 * Get the person who made the last for this page
	 *
	 * @return User
	 */
	public function getUser() {
		$this->init();
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
		$this->init();
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
		$this->init();
		$logLine = $this->getLog()->current();
		if ( $logLine ) {
			return $logLine->log_action;
		}
	}
}
