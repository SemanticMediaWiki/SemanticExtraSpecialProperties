<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;
use MediaWiki\User\User;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\DataItem;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class CreatorPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___CUSER';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @since 2.0
	 *
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$page = $this->appFactory->newWikiPage( $semanticData->getSubject()->getTitle() );
		$dataItem = null;

		$creator = $page->getCreator();
		if ( $creator ) {
			if ( !( $creator instanceof User ) ) {
				$creator = MediaWikiServices::getInstance()->getUserFactory()->newFromUserIdentity( $creator );
			}
			if ( ( $userPage = $creator->getUserPage() ) instanceof Title ) {
				$dataItem = WikiPage::newFromTitle( $userPage );
			}
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
