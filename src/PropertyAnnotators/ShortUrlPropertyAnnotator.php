<?php

namespace SESP\PropertyAnnotators;

use MediaWiki\Title\Title;
use RuntimeException;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataModel\SemanticData;
use SMW\DataItems\DataItem;
use SMW\DataItems\Uri;
/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 * @author rotsee
 */
class ShortUrlPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___SHORTURL';

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
		if ( !$this->hasShortUrlUtils() ) {
			throw new RuntimeException( 'Class ShortUrlUtils is not available' );
		}

		$dataItem = null;
		$shortUrl = $this->getShortUrl( $semanticData->getSubject()->getTitle() );

		if ( $shortUrl !== null ) {
			$dataItem = new Uri( 'http', $shortUrl, '', '' );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param Title $title
	 */
	protected function getShortUrl( Title $title ) {
		// FIXME handle internal and external links
		$shortUrl = null;

		if ( \ShortUrlUtils::needsShortUrl( $title ) ) {
			$shortUrl = $this->getUrlPrefix() . \ShortUrlUtils::encodeTitle( $title );
		}

		return $shortUrl;
	}

	/**
	 * get prefix Url
	 */
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
