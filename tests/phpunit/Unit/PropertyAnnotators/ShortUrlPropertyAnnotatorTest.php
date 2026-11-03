<?php

namespace SESP\Tests\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotators\ShortUrlPropertyAnnotator;
use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;

/**
 * @covers \SESP\PropertyAnnotators\ShortUrlPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ShortUrlPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $property;
	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();

		$this->property = new Property( '___SHORTURL' );
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ShortUrlPropertyAnnotator::class,
			new ShortUrlPropertyAnnotator( $this->appFactory )
		);
	}

	public function testIsAnnotatorFor() {
		$instance = new ShortUrlPropertyAnnotator(
			$this->appFactory
		);

		$this->assertTrue(
			$instance->isAnnotatorFor( $this->property )
		);
	}

	public function testUrlPrefixDefaultsToTheShortUrlSpecialPage() {
		$this->appFactory->method( 'getOption' )
			->with( 'wgShortUrlPrefix', '' )
			->willReturn( '' );

		// The ShortUrl extension defines the alias of its special page and is not installed in the test
		// environment, so MediaWiki would raise a development warning about the missing alias. That is
		// unrelated to what is tested here, so only this call runs without development warnings.
		global $wgDevelopmentWarnings;
		$developmentWarnings = $wgDevelopmentWarnings;
		$wgDevelopmentWarnings = false;

		try {
			$prefix = $this->newInstanceExposingUrlPrefix()->urlPrefix();
		} finally {
			$wgDevelopmentWarnings = $developmentWarnings;
		}

		$this->assertStringContainsString( 'ShortUrl', $prefix );
		$this->assertStringEndsWith( '/', $prefix );
	}

	public function testConfiguredUrlPrefixIsUsedAsIs() {
		$this->appFactory->method( 'getOption' )
			->with( 'wgShortUrlPrefix', '' )
			->willReturn( 'https://example.org/s/' );

		$this->assertSame(
			'https://example.org/s/',
			$this->newInstanceExposingUrlPrefix()->urlPrefix()
		);
	}

	private function newInstanceExposingUrlPrefix(): ShortUrlPropertyAnnotator {
		return new class( $this->appFactory ) extends ShortUrlPropertyAnnotator {
			public function urlPrefix() {
				return $this->getUrlPrefix();
			}
		};
	}

	public function testMissingShortUrlUtilsThrowsException() {
		// PHPUnit 6.5+
		if ( is_callable( [ $this, 'expectException' ] ) ) {
			$this->expectException( '\RuntimeException' );
		} else {
			$this->setExpectedException( '\RuntimeException' );
		}

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$instance = $this->getMockBuilder( ShortUrlPropertyAnnotator::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'hasShortUrlUtils' ] )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->willReturn( false );

		$instance->addAnnotation( $this->property, $semanticData );
	}

	public function testAddAnnotation() {
		$subject = WikiPage::newFromText( __METHOD__ );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$semanticData->expects( $this->once() )
			->method( 'addPropertyObjectValue' );

		$instance = $this->getMockBuilder( ShortUrlPropertyAnnotator::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'hasShortUrlUtils', 'getShortUrl' ] )
			->getMock();

		$instance->expects( $this->once() )
			->method( 'hasShortUrlUtils' )
			->willReturn( true );

		$instance->expects( $this->once() )
			->method( 'getShortUrl' )
			->willReturn( 'foo' );

		$instance->addAnnotation( $this->property, $semanticData );
	}

}
