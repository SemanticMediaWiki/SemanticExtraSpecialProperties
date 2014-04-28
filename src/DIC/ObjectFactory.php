<?php

namespace SESP\DIC;

use Onoi\DependencyInjection\Container;
use Onoi\DependencyInjection\BuilderFactory;
use Onoi\DependencyInjection\DependencyBuilder;

use Title;

use InvalidArgumentException;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.3.0
 *
 * @author mwjames
 */
class ObjectFactory {

	protected static $instance = null;
	protected $dependencyBuilder;

	protected function __construct( DependencyBuilder $dependencyBuilder ) {
		$this->dependencyBuilder = $dependencyBuilder;
	}

	/**
	 * @since 1.3.0
	 *
	 * @return ServiceFactory
	 */
	public static function getInstance() {

		if ( self::$instance === null ) {
			self::$instance = new self( self::newDependencyBuilder() );
			self::$instance->registerContainer( new DIContainer() );
		}

		return self::$instance;
	}

	/**
	 * @since 1.3.0
	 */
	public static function clear() {
		self::$instance = null;
	}

	/**
	 * @since 1.3.0
	 *
	 * @param string $objectName
	 * @param Closure|array $objectSignature
	 */
	public function registerObject( $objectName, $objectSignature ) {
		$this->dependencyBuilder->registerObject( $objectName, $objectSignature );
	}

	/**
	 * @since 1.3.0
	 *
	 * @param Container $container
	 */
	public function registerContainer( Container $container ) {
		$this->dependencyBuilder->registerContainer( $container );
	}

	/**
	 * @since 1.3.0
	 *
	 * @param Title $title
	 *
	 * @return WikiPage
	 */
	public function newWikiPage( Title $title ) {
		return $this->dependencyBuilder->byDefinition( 'mw.wikipage' )
			->withArgument( 'mw.title', $title )
			->build();
	}

	/**
	 * @since 1.3.0
	 *
	 * @return DatabaseBase
	 */
	public function getDBConnection( $connectionId ) {
		return $this->dependencyBuilder->byDefinition( 'mw.dbconnection' )
			->withArgument( 'mw.db.connectionId', $connectionId )
			->build();
	}

	/**
	 * @since 1.3.0
	 *
	 * @return User
	 */
	public function newUserFromName( Title $title ) {
		return $this->dependencyBuilder->byDefinition( 'mw.user.fromName' )
			->withArgument( 'mw.title', $title )
			->build();
	}

	/**
	 * @since 1.3.0
	 *
	 * @return User
	 */
	public function newUserFromId( $id ) {
		return $this->dependencyBuilder->byDefinition( 'mw.user.fromId' )
			->withArgument( 'mw.userId', $id )
			->build();
	}

	private static function newDependencyBuilder() {
		return BuilderFactory::getInstance()->newDependencyBuilder();
	}

}
