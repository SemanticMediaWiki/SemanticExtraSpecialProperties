<?php

namespace SESP;

use SMW\Localizer\Message;
use Wikimedia\ObjectCache\BagOStuff;
use Wikimedia\ObjectCache\EmptyBagOStuff;

/**
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class LabelFetcher {

	/**
	 * Namespace of the cache instance
	 */
	public const LABEL_CACHE_NAMESPACE = 'sesp:labels';

	/**
	 * @var Cache
	 */
	private $cache;

	/**
	 * @var string
	 */
	private $languageCode = 'en';

	/**
	 * @var string
	 */
	private $labelCacheVersion = 0;

	/**
	 * @since 2.0
	 *
	 * @param BagOStuff|null $cache
	 * @param string $languageCode
	 */
	public function __construct( ?BagOStuff $cache = null, $languageCode = 'en' ) {
		$this->cache = $cache;
		$this->languageCode = $languageCode;

		if ( $this->cache === null ) {
			$this->cache = new EmptyBagOStuff();
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param int|string $labelCacheVersion
	 */
	public function setLabelCacheVersion( $labelCacheVersion ) {
		$this->labelCacheVersion = $labelCacheVersion;
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getLabel( $key ) {
		return Message::get( $key, null, Message::USER_LANGUAGE );
	}

	/**
	 * @since 2.0
	 *
	 * @param PropertyDefinitions $propertyDefinitions
	 *
	 * @return array
	 */
	public function getLabelsFrom( PropertyDefinitions $propertyDefinitions ) {
		$hash = smwfCacheKey(
			self::LABEL_CACHE_NAMESPACE,
			[
				$propertyDefinitions,
				$this->languageCode,
				$this->labelCacheVersion
			]
		);

		if ( $this->labelCacheVersion !== false && ( $labels = $this->cache->get( $hash ) ) !== false ) {
			return $labels;
		}

		$labels = [];
		$exifDefinitions = [];

		foreach ( $propertyDefinitions as $key => $definition ) {
			$this->matchLabel( $labels, $definition );
		}

		foreach ( $propertyDefinitions->safeGet( '_EXIF', [] ) as $key => $definition ) {
			$this->matchLabel( $labels, $definition );
		}

		if ( $labels !== [] ) {
			$this->cache->save( $hash, $labels, 3600 * 24 );
		}

		return $labels;
	}

	private function matchLabel( &$labels, $definition ) {
		if ( !isset( $definition['id'] ) ) {
			return;
		}

		$alias = 'sesp-property-unknown-label';

		if ( isset( $definition['alias'] ) ) {
			$alias = $definition['alias'];
		}

		$labels[$definition['id']] = Message::get( $alias, null, $this->languageCode );
	}

}
