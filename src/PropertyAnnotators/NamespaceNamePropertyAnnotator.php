<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\MediaWikiServices;
use RequestContext;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;
use SMW\DataItems\Blob;
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
	public const PROP_ID = '___NSNAME';

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
	public function isAnnotatorFor( Property $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * {@inheritDoc}
	 */
	public function addAnnotation( Property $property, SemanticData $semanticData ) {
		$nsInfo = MediaWikiServices::getInstance()->getNamespaceInfo();
		$namespaceName = $nsInfo->getCanonicalName( $semanticData->getSubject()->getTitle()->getNamespace() );
		if ( $namespaceName !== false ) {
			if ( $namespaceName === '' ) {
				$namespaceName = RequestContext::getMain()->msg( 'blanknamespace' )->text();
			}
			$dataItem = new Blob( $namespaceName );
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}
}
