<?php

namespace SESP\Tests\Hooks;

use MediaWiki\Title\Title;
use SESP\Hooks\ApprovedRevsHooks;
use Wikimedia\ObjectCache\BagOStuff;

/**
 * @covers \SESP\Hooks\ApprovedRevsHooks
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 5.0.0
 */
class ApprovedRevsHooksTest extends \PHPUnit\Framework\TestCase {

	private function newTitle( string $dbKey ): Title {
		$title = $this->createMock( Title::class );
		$title->method( 'getPrefixedDBKey' )->willReturn( $dbKey );

		return $title;
	}

	public function testRevisionApprovedCachesTheApprovedRevisionId() {
		$cache = $this->createMock( BagOStuff::class );
		$cache->expects( $this->once() )
			->method( 'set' )
			->with(
				smwfCacheKey( 'smw:parseraftertidy', 'Foo' ),
				4242,
				60 * 60
			);

		$instance = new ApprovedRevsHooks( $cache );

		$this->assertTrue(
			$instance->onApprovedRevsRevisionApproved( null, $this->newTitle( 'Foo' ), 4242, null )
		);
	}

	public function testRevisionUnapprovedClearsTheCachedRevisionId() {
		$cache = $this->createMock( BagOStuff::class );
		$cache->expects( $this->once() )
			->method( 'set' )
			->with(
				smwfCacheKey( 'smw:parseraftertidy', 'Foo' ),
				null,
				60 * 60
			);

		$instance = new ApprovedRevsHooks( $cache );

		$this->assertTrue(
			$instance->onApprovedRevsRevisionUnapproved( null, $this->newTitle( 'Foo' ), null )
		);
	}
}
