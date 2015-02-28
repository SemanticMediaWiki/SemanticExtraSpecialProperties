<?php

namespace SESP\Annotator;

use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIProperty;

use SMWDIUri as DIUri;

use Title;
use SpecialPage;
use RuntimeException;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author rotsee
 */
class ShortUrlAnnotator {

	/**
	 * @var SemanticData
	 */
	private $semanticData  = null;

	/**
	 * @var string
	 */
	private $shortUrlPrefix = '';

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 */
	public function __construct( SemanticData $semanticData ) {
		$this->semanticData = $semanticData;
	}

	/**
	 * @since 1.3
	 *
	 * @return boolean
	 */
	public function canUseShortUrl() {
		return class_exists( 'ShortUrlUtils' );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $shortUrlPrefix
	 */
	public function setShortUrlPrefix( $shortUrlPrefix ) {
		$this->shortUrlPrefix = $shortUrlPrefix;
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function addAnnotation() {

		if ( !$this->hasShortUrlUtils() ) {
			throw new RuntimeException( 'Expected class ShortUrlUtils to be available' );
		}

		$shortURL = $this->getShortUrl( $this->semanticData->getSubject()->getTitle() );

		if ( $shortURL !== null ) {
			$this->semanticData->addPropertyObjectValue(
				new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '_SHORTURL' ) ),
				new DIUri( 'http', $shortURL, '', '' )
			);
		}

		return true;
	}

	protected function getShortUrl( Title $title ) {

		//FIXME handle internal and external links
		$shortURL = null;

		if ( \ShortUrlUtils::needsShortUrl( $title ) ) {
			$shortURL = $this->getUrlPrefix() . \ShortUrlUtils::encodeTitle( $title );
		}

		return $shortURL;
	}

	protected function getUrlPrefix() {

		if ( $this->shortUrlPrefix === '' ) {
			return SpecialPage::getTitleFor( 'ShortUrl' )->getFullUrl() . '/';
		}

		return $this->shortUrlPrefix;
	}

	protected function hasShortUrlUtils() {
		return class_exists( 'ShortUrlUtils' );
	}

}
