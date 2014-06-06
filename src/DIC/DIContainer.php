<?php

namespace SESP\DIC;

use Onoi\DependencyInjection\Container;
use Onoi\DependencyInjection\DependencyBuilder;

use WikiPage;
use User;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.3.0
 *
 * @author mwjames
 */
class DIContainer implements Container {

	protected $configuration = array();

	public function __construct( array $configuration = array() ) {
		$this->configuration = $configuration;
	}

	public function loadDefinitions( DependencyBuilder $dependencyBuilder ) {

		$dependencyBuilder->registerObject( 'sesp.configuration', $this->configuration );

		$dependencyBuilder->registerType( 'smw.subject', '\SMW\DIWikiPage' );

		$dependencyBuilder
			->asSingleton()
			->registerType( 'mw.dbconnection', '\DatabaseBase' )
			->registerObject( 'mw.dbconnection', function( DependencyBuilder $dependencyBuilder ) {
			return wfGetDB( $dependencyBuilder->getArgumentValue( 'mw.db.connectionId' ) );
		} );

		$dependencyBuilder
			->registerType( 'mw.wikipage', '\WikiPage' )
			->registerObject( 'mw.wikipage', function( DependencyBuilder $dependencyBuilder ) {
			return WikiPage::factory( $dependencyBuilder->getArgumentValue( 'mw.title' ) );
		} );

		$dependencyBuilder
			->registerType( 'mw.user.fromname', array( '\User', 'boolean' ) )
			->registerObject( 'mw.user.fromname', function( DependencyBuilder $dependencyBuilder ) {
			return User::newFromName( $dependencyBuilder->getArgumentValue( 'mw.title' )->getText() );
		} );

		$dependencyBuilder
			->registerType( 'mw.user.fromid', array( '\User' ) )
			->registerObject( 'mw.user.fromid', function( DependencyBuilder $dependencyBuilder ) {
			return User::newFromId( $dependencyBuilder->getArgumentValue( 'mw.userId' ) );
		} );

	}

}
