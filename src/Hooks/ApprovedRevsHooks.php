<?php

namespace SESP\Hooks;

use MediaWiki\Title\Title;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * Integrates with the Approved Revs extension.
 *
 * When an approval changes, an event is primed for Semantic MediaWiki's
 * ParserAfterTidy so that the data update is carried out even when the new
 * content contains no SMW annotations (e.g. switching to a blank revision),
 * allowing the store to drop any remaining annotations.
 *
 * The hook signatures are dictated by the Approved Revs extension, so the
 * parameters are left untyped to match its contract.
 *
 * @license GPL-2.0-or-later
 * @since 5.0.0
 */
class ApprovedRevsHooks {

	private const TTL = 60 * 60;

	public function __construct( private readonly BagOStuff $cache ) {
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Extension:Approved_Revs/Hooks/ApprovedRevsRevisionApproved
	 * @since 5.0.0
	 *
	 * @param mixed $output
	 * @param Title $title
	 * @param int $rev_id
	 * @param mixed $content
	 */
	public function onApprovedRevsRevisionApproved( $output, $title, $rev_id, $content ): bool {
		$this->cache->set( $this->makeCacheKey( $title ), $rev_id, self::TTL );

		return true;
	}

	/**
	 * @see https://www.mediawiki.org/wiki/Extension:Approved_Revs/Hooks/ApprovedRevsRevisionUnapproved
	 * @since 5.0.0
	 *
	 * @param mixed $output
	 * @param Title $title
	 * @param mixed $content
	 */
	public function onApprovedRevsRevisionUnapproved( $output, $title, $content ): bool {
		$this->cache->set( $this->makeCacheKey( $title ), null, self::TTL );

		return true;
	}

	private function makeCacheKey( $title ): string {
		return smwfCacheKey( 'smw:parseraftertidy', $title->getPrefixedDBKey() );
	}
}
