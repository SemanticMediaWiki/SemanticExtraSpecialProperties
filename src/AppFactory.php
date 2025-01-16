<?php

namespace SESP;

use MediaWiki\MediaWikiServices;
use Onoi\Cache\Cache;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Title;
use User;
use Wikimedia\Rdbms\Database;
use WikiPage;

/**
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 1.3
 *
 * @author mwjames
 */
class AppFactory implements LoggerAwareInterface {

	/**
	 * @var array
	 */
	private $options;

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var array
	 */
	private $connection;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var PropertyDefinitions
	 */
	private $propertyDefinitions;

	/**
	 * @since 2.0
	 *
	 * @param array $options
	 * @param Cache|null $cache
	 */
	public function __construct( array $options = [], Cache $cache = null ) {
		$this->options = $options;
		$this->cache = $cache;
	}

	/**
	 * @since 2.0
	 */
	public function setConnection( Database $connection ) {
		$this->connection = $connection;
	}

	/**
	 * @since 1.3
	 *
	 * @return Database
	 */
	public function getConnection() {
		if ( $this->connection === null ) {
			if ( version_compare( MW_VERSION, '1.42', '>=' ) ) {
				$this->connection = MediaWikiServices::getInstance()->getConnectionProvider()->getReplicaDatabase();
			} else {
				$this->connection = MediaWikiServices::getInstance()->getDBLoadBalancer()->getConnection( DB_REPLICA );
			}
		}

		return $this->connection;
	}

	/**
	 * @see LoggerAwareInterface::setLogger
	 *
	 * @since 2.0
	 *
	 * @param LoggerInterface $logger
	 */
	public function setLogger( LoggerInterface $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @since 2.0
	 *
	 * @return LoggerInterface
	 */
	public function getLogger() {
		if ( $this->logger === null ) {
			return new NullLogger();
		}

		return $this->logger;
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed|false
	 */
	public function getOption( $key, $default = false ) {
		if ( isset( $this->options[$key] ) ) {
			return $this->options[$key];
		}

		return $default;
	}

	/**
	 * @since 2.0
	 *
	 * @return PropertyDefinitions
	 */
	public function getPropertyDefinitions() {
		if ( $this->propertyDefinitions !== null ) {
			return $this->propertyDefinitions;
		}

		$labelFetcher = new LabelFetcher(
			$this->cache,
			$GLOBALS['wgLang']->getCode()
		);

		$labelFetcher->setLabelCacheVersion(
			$this->getOption( 'sespgLabelCacheVersion', 0 )
		);

		$this->propertyDefinitions = new PropertyDefinitions(
			$labelFetcher,
			$this->getOption( 'sespgDefinitionsFile' )
		);

		$this->propertyDefinitions->setLocalPropertyDefinitions(
			$this->getOption( 'sespgLocalDefinitions', [] )
		);

		return $this->propertyDefinitions;
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 *
	 * @return WikiPage
	 */
	public function newWikiPage( Title $title ) {
		// #55
		// Fight a possible DB corruption and avoid "NS_MEDIA is a virtual namespace; use NS_FILE"
		if ( $title->getNamespace() === NS_MEDIA ) {
			$title = Title::makeTitleSafe(
				NS_FILE,
				$title->getDBkey(),
				$title->getInterwiki(),
				$title->getFragment()
			);
		}

		$services = MediaWikiServices::getInstance();
		return $services->getWikiPageFactory()->newFromTitle( $title );
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 *
	 * @return User
	 */
	public function newUserFromTitle( Title $title ) {
		return User::newFromName( $title->getText() );
	}

	/**
	 * @since 1.3
	 *
	 * @param int $id
	 *
	 * @return User
	 */
	public function newUserFromID( $id ) {
		return User::newFromId( $id );
	}

	/**
	 * @since 2.0
	 *
	 * @param null|Title $title to get the DBLogReader
	 * @param string $type which log entries to get (default: approval)
	 * @return DatabaseLogReader
	 */
	public function newDatabaseLogReader( Title $title = null, $type = 'approval' ) {
		return new DatabaseLogReader( $this->getConnection(), $title, $type );
	}
}
