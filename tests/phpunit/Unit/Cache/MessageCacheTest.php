<?php

namespace SESP\Tests\Cache;

use SESP\Cache\MessageCache;

use Language;
use HashBagOStuff;

/**
 * @covers \SESP\Cache\MessageCache
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.1.1
 *
 * @author mwjames
 */
class MessageCacheTest extends \PHPUnit_Framework_TestCase {

	protected $cacheId = 'sesp:foo';

	public function testCanConstruct() {

		$language = $this->getMockBuilder( 'Language' )
			->disableOriginalConstructor()
			->getMock();

		$touched = 1000;

		$this->assertInstanceOf(
			'\SESP\Cache\MessageCache',
			new MessageCache( $language, $touched )
		);
	}

	public function testAccessibility() {

		$instance = new MessageCache(
			Language::factory( 'en' ),
			10001
		);

		$instance->setCache( new HashBagOStuff );
		$this->assertInternalType( 'string', $instance->get( 'foo' ) );
	}

	public function testAddNewCachedMessage() {

		$cache = new HashBagOStuff;

		$instance = $this->acquireInstanceWith( 1000 );
		$instance->setCache( $cache );

		$this->assertFalse( $cache->get( $this->cacheId ) );

		$this->assertInternalType( 'string', $instance->get( 'exif-software' ) );
		$this->assertInternalType( 'array', $cache->get( $this->cacheId ) );

		$this->assertArrayHasKey( 'touched', $cache->get( $this->cacheId ) );
		$this->assertArrayHasKey( 'messages', $cache->get( $this->cacheId ) );
	}

	public function testGetCachedMessage() {

		$cacheTimeOffset = 9999;

		$cache = new HashBagOStuff;

		$presetCached = array(
			'touched'  => $cacheTimeOffset . 1000,
			'messages' => array( 'foo' => 'bar' )
		);

		$cache->set( $this->cacheId, $presetCached );

		$instance = $this->acquireInstanceWith( 1000, $cacheTimeOffset );
		$instance->setCache( $cache );

		$this->assertEquals( 'bar', $instance->get( 'foo' ) );
	}

	public function testCachePurgeByLanguage() {

		$cache = new HashBagOStuff;

		$instanceJa = MessageCache::byLanguage( Language::factory( 'ja' ) );
		$instanceEn = MessageCache::byLanguage( Language::factory( 'en' ) );

		$presetCached = array(
			'touched'  => 1000,
			'messages' => array( 'foo' => 'bar' )
		);

		$cache->set( $instanceEn->getCacheId(), $presetCached );
		$cache->set( $instanceJa->getCacheId(), $presetCached );

		$instanceEn->setCache( $cache )->purge();
		$instanceJa->setCache( $cache );

		$this->assertEmpty( $cache->get( $instanceEn->getCacheId() ) );
		$this->assertNotEmpty( $cache->get( $instanceJa->getCacheId() ) );
	}

	protected function acquireInstanceWith( $modificationTimeOffset, $cacheTimeOffset = null ) {

		$language = Language::factory( 'en' );

		$instance = $this->getMock( '\SESP\Cache\MessageCache',
			array(
				'getCacheId',
				'getMessageFileModificationTime' ),
			array(
				$language,
				$modificationTimeOffset )
		);

		$instance->expects( $this->atLeastOnce() )
			->method( 'getCacheId' )
			->will( $this->returnValue( $this->cacheId ) );

		$instance->expects( $this->atLeastOnce() )
			->method( 'getMessageFileModificationTime' )
			->will( $this->returnValue( $cacheTimeOffset ) );

		return $instance;
	}

}