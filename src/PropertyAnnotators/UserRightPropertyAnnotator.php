<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use MediaWiki\Permissions\PermissionManager;
use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDIBlob as DIBlob;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use User;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class UserRightPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___USERRIGHT';

	/**
	 * @var AppFactory
	 */
	private $appFactory;
	private PermissionManager $permissionManager;

	/**
	 * @since 2.0
	 *
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
		$this->permissionManager = MediaWikiServices::getInstance()->getPermissionManager();
	}

	public function setPermissionManager( PermissionManager $permissionManager ) {
		$this->permissionManager = $permissionManager;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		$title = $semanticData->getSubject()->getTitle();

		if ( !$title->inNamespace( NS_USER ) ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle(
			$title
		);

		if ( !$user instanceof User ) {
			return;
		}

		foreach ( $this->permissionManager->getUserPermissions( $user ) as $right ) {
			$semanticData->addPropertyObjectValue( $property, new DIBlob( $right ) );
		}
	}

}
