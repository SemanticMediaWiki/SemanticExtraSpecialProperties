<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDINumber as DINumber;
use SESP\PropertyAnnotator;
use SESP\AppFactory;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PageContributorsPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___EUSER';

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
		$page = $this->appFactory->newWikiPage( $title );

		$user = $this->appFactory->newUserFromID( $page->getUser() );
		$authors = $page->getContributors();

		$dataItem = null;

		while ( $user ) {
			 //no anonymous users (hidden users are not returned)
			if ( $this->isNotAnonymous( $user ) ) {
				$semanticData->addPropertyObjectValue(
					$property,
					DIWikiPage::newFromTitle( $user->getUserPage() )
				);
			}

			$user = $authors->current();
			$authors->next();
		}
	}

	private function isNotAnonymous( $user ) {
		return !( in_array( 'bot', $user->getRights() ) && $this->appFactory->getOption( 'sespgExcludeBotEdits' ) ) && !$user->isAnon();
	}

}
