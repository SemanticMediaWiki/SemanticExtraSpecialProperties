<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use Title;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 3.0.4
 */
class LinksToPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___LINKSTO';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	private $enabledNamespaces;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;

		$this->enabledNamespaces = MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'sespg' )
			->get( 'LinksToEnabledNamespaces' );
	}

	public function setEnabledNamespaces( array $namespaces ) {
		$this->enabledNamespaces = $namespaces;
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
		$page = $semanticData->getSubject()->getTitle();

		if (
			$page === null ||
			( !empty( $this->enabledNamespaces ) &&
			!$page->inNamespaces( $this->enabledNamespaces ) )
		) {
			return;
		}

		$con = $this->appFactory->getConnection();

		if ( $con === null ) {
			return;
		}

		$where = [];
		$where[] = sprintf( 'pl.pl_from = %s', $page->getArticleID() );
		$where[] = sprintf( 'pl.pl_title != %s', $con->addQuotes( $page->getDBkey() ) );
		$where[] = sprintf( 'pl.pl_namespace IN (%s)', implode( ',', $this->enabledNamespaces ) );

		$res = $con->select(
			[ 'pl' => 'pagelinks', 'page' ],
			[ 'sel_title' => 'pl.pl_title', 'sel_ns' => 'pl.pl_namespace' ],
			$where,
			__METHOD__,
			[ 'DISTINCT' ],
			[ 'page' => [ 'JOIN', 'page_id=pl_from' ] ]
		);

		foreach ( $res as $row ) {
			$title = Title::newFromText( $row->sel_title, $row->sel_ns );
			if ( $title !== null && $title->exists() ) {
				$semanticData->addPropertyObjectValue( $property, DIWikiPage::newFromTitle( $title ) );
			}
		}
	}
}
