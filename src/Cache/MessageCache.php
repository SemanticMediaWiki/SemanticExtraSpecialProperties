<?php

namespace SESP\Cache;

use ObjectCache;
use Language;
use BagOStuff;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 1.2.0
 *
 * @author mwjames
 */
class MessageCache {

	/** @var Language */
	protected $language = null;

	protected $touched = null;
	protected $cacheTimeOffset = null;
	protected $messages = null;
	protected $cache = null;

	/**
	 * @since 1.2.0
	 *
	 * @param Language $language
	 * @param integer|null $cacheTimeOffset
	 */
	public function __construct( Language $language, $cacheTimeOffset = null ) {
		$this->language = $language;
		$this->cacheTimeOffset = $cacheTimeOffset;
	}

	/**
	 * @since 1.2.0
	 *
	 * @param Language $language
	 *
	 * @return MessageCache
	 */
	public static function byLanguage( Language $language ) {
		return new self( $language );
	}

	/**
	 * @since 1.2.0
	 *
	 * MessageCache::byLanguage( Language::factory( 'en' ) )->purge()
	 *
	 * @return MessageCache
	 */
	public function purge() {
		$this->getCache()->delete( $this->getCacheId() );
		return $this;
	}

	/**
	 * @since 1.2.0
	 *
	 * @param integer $cacheTimeOffset
	 *
	 * @return MessageCache
	 */
	public function setCacheTimeOffset( $cacheTimeOffset ) {
		$this->cacheTimeOffset = $cacheTimeOffset;
		return $this;
	}

	/**
	 * @since 1.2.0
	 *
	 * @param BagOStuff $cache
	 */
	public function setCache( BagOStuff $cache ) {
		$this->cache = $cache;
		return $this;
	}

	/**
	 * @since 1.2.0
	 *
	 * @return string
	 */
	public function getCacheId() {
		return $this->getCachePrefix() . ':sesp:mcache:' . $this->language->getCode();
	}

	/**
	 * @since 1.2.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function get( $key /* arguments */ ) {

		$arguments = func_get_args();

		if ( $this->messages === null ) {
			$this->messages = $this->loadMessagesFromCache();
		}

		$key = implode( '#', $arguments );

		if ( isset( $this->messages[ $key ] ) ) {
			return $this->messages[ $key ];
		}

		return $this->getTextMessage( $key, $arguments );
	}

	protected function getTextMessage( $key, $arguments ) {

		$this->messages[ $key ] = wfMessage( $arguments )->inLanguage( $this->language )->text();
		$this->updateMessagesToCache();

		return $this->messages[ $key ];
	}

	protected function updateMessagesToCache() {

		$messagesToBeCached = array(
			'touched'  => $this->getTouched(),
			'messages' => $this->messages
		);

		return $this->getCache()->set( $this->getCacheId(), $messagesToBeCached );
	}

	protected function loadMessagesFromCache() {

		$cached = $this->getCache()->get( $this->getCacheId() );

		if ( isset( $cached['touched'] ) && isset( $cached['messages'] ) && $cached['touched'] === $this->getTouched() ) {
			return $cached['messages'];
		}

		return null;
	}

	protected function getCache() {

		if ( !$this->cache instanceOf BagOStuff ) {
			$this->cache = ObjectCache::getInstance( $GLOBALS['sespCacheType'] );
		}

		return $this->cache;
	}

	protected function getCachePrefix() {
		return $GLOBALS['wgCachePrefix'] === false ? wfWikiID() : $GLOBALS['wgCachePrefix'];
	}

	protected function getTouched() {

		if ( $this->touched === null ) {
			$this->touched = $this->getMessageFileModificationTime() . $this->cacheTimeOffset;
		}

		return $this->touched;
	}

	protected function getMessageFileModificationTime() {

		if ( method_exists( $this->language, 'getJsonMessagesFileName' )  ) {
			return filemtime( $this->language->getJsonMessagesFileName( $this->language->getCode() ) );
		}

		return filemtime( $this->language->getMessagesFileName( $this->language->getCode() ) );
	}

}