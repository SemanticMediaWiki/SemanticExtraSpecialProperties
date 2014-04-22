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
class ShortUrlAnnotator extends BaseAnnotator {

	/** @var SemanticData */
	protected $semanticData  = null;

	/** @var array */
	protected $configuration = null;

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 * @param array $configuration
	 */
	public function __construct( SemanticData $semanticData, array $configuration ) {
		$this->semanticData = $semanticData;
		$this->configuration = $configuration;
	}

	/**
	 * @since 1.0
	 *
	 * @param SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
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

		$shortURL = $this->getShortUrl( $this->getSemanticData()->getSubject()->getTitle() );

		if ( $shortURL !== null ) {
			$this->getSemanticData()->addPropertyObjectValue(
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

		if ( !isset( $this->configuration['wgShortUrlPrefix'] ) && !is_string( $this->configuration['wgShortUrlPrefix'] ) ) {
			return SpecialPage::getTitleFor( 'ShortUrl' )->getFullUrl() . '/';
		}

		return $this->configuration['wgShortUrlPrefix'];
	}

	protected function hasShortUrlUtils() {
		return class_exists( 'ShortUrlUtils' );
	}

}
