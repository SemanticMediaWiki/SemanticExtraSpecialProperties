<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use RequestContext;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMWDIBlob;
use SMW\DIProperty;
use SMW\SemanticData;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 */
class NamespaceNamePropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___NSNAME';

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		$namespaceName = $nsInfo->getCanonicalName( $semanticData->getSubject()->getTitle()->getNamespace() );
		if ( $namespaceName !== false ) {
			if ( $namespaceName === '' ) {
				$namespaceName = RequestContext::getMain()->msg( 'blanknamespace' )->text();
			}
			$dataItem = new SMWDIBlob( $namespaceName );
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}
}
