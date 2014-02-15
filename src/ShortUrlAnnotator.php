<?php

namespace SESP;

use SMW\SemanticData;
use SMW\DIProperty;

use SMWDIUri as DIUri;

use SpecialPage;
use RuntimeException;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author rotsee
 */
class ShortUrlAnnotator extends BaseAnnotator {

	/** @var SemanticData */
	protected $semanticData  = null;

	/** @var array */
	protected $configuration = null;

	/**
	 * @since 0.3
	 *
	 * @param SemanticData $semanticData
	 * @param array $configuration
	 */
	public function __construct( SemanticData $semanticData, array $configuration ) {
		$this->semanticData = $semanticData;
		$this->configuration = $configuration;
	}

	/**
	 * @since 0.3
	 *
	 * @param SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
	}

	/**
	 * @since 0.3
	 *
	 * @return boolean
	 */
	public function addAnnotation() {

		if ( !class_exists( 'ShortUrlUtils' ) ) {
			throw new RuntimeException( 'Expected class ShortUrlUtils to be available' );
		}

		$title = $this->getSemanticData()->getSubject()->getTitle();

		//FIXME handle internal and external links

		if ( \ShortUrlUtils::needsShortUrl( $title ) ) {
			$shortId = \ShortUrlUtils::encodeTitle( $title );
			$shortURL = $this->getUrlPrefix() . $shortId;

			$this->getSemanticData()->addPropertyObjectValue(
				new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '_SHORTURL' ) ),
				new DIUri( 'http', $shortURL, '', '' )
			);
		}

		return true;
	}

	protected function getUrlPrefix () {

		if ( !isset( $this->configuration['wgShortUrlPrefix'] ) && !is_string( $this->configuration['wgShortUrlPrefix'] ) ) {
			return SpecialPage::getTitleFor( 'ShortUrl' )->getFullUrl() . '/';
		}

		return $this->configuration['wgShortUrlPrefix'];
	}

}
