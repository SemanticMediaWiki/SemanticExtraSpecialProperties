<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SMWDIUri as DIUri;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use Title;
use RuntimeException;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 * @author rotsee
 */
class ShortUrlPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	const PROP_ID = '___SHORTURL';

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

		if ( !$this->hasShortUrlUtils() ) {
			throw new RuntimeException( 'Class ShortUrlUtils is not available' );
		}

		$dataItem = null;
		$shortUrl = $this->getShortUrl( $semanticData->getSubject()->getTitle() );

		if ( $shortUrl !== null ) {
			$dataItem = new DIUri( 'http', $shortUrl, '', '' );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

	protected function getShortUrl( Title $title ) {

		//FIXME handle internal and external links
		$shortUrl = null;

		if ( \ShortUrlUtils::needsShortUrl( $title ) ) {
			$shortUrl = $this->getUrlPrefix() . \ShortUrlUtils::encodeTitle( $title );
		}

		return $shortUrl;
	}

	protected function getUrlPrefix() {

		$shortUrlPrefix = $this->appFactory->getOption( 'wgShortUrlPrefix', '' );

		if ( $shortUrlPrefix === '' ) {
			return SpecialPage::getTitleFor( 'ShortUrl' )->getFullUrl() . '/';
		}

		return $shortUrlPrefix;
	}

	protected function hasShortUrlUtils() {
		return class_exists( 'ShortUrlUtils' );
	}

}
