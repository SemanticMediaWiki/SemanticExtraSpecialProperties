<?php

namespace SESP;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Onoi\Cache\Cache;
use Onoi\Cache\NullCache;
use Title;
use WikiPage;
use User;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
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
	public function setConnection( \DatabaseBase $connection ) {
		$this->connection = $connection;
	}

	/**
	 * @since 1.3
	 *
	 * @return DatabaseBase
	 */
	public function getConnection() {

		if ( $this->connection === null ) {
			$this->connection = wfGetDB( DB_SLAVE );
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
	 * @param LoggerInterface
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
	 * @param $default $mixed
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
			$this->getOption( 'sespLabelCacheVersion', 0 )
		);

		$this->propertyDefinitions = new PropertyDefinitions(
			$labelFetcher,
			$this->getOption( 'sespPropertyDefinitionFile' )
		);

		$this->propertyDefinitions->setLocalPropertyDefinitions(
			$this->getOption( 'sespLocalPropertyDefinitions', [] )
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

		return WikiPage::factory( $title );
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
	 * @param $id
	 *
	 * @return User
	 */
	public function newUserFromID( $id ) {
		return User::newFromId( $id );
	}

}
