<?php

namespace SESP;

use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
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
	 */
	public function __construct( array $options = array() ) {
		$this->options = $options;
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
	 * @since 3.0
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
	 * @since 2.4
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

		$this->propertyDefinitions = new PropertyDefinitions(
			$this->getOption( 'sespPropertyDefinitionFile' )
		);

		$this->propertyDefinitions->setLocalPropertyDefinitions(
			$this->getOption( 'sespLocalPropertyDefinitions', array() )
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
