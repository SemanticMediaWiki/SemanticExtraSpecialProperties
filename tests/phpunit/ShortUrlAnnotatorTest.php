<?php

namespace SESP\Tests;

use SESP\ShortUrlAnnotator;

/**
 * @covers \SESP\ShortUrlAnnotator
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 */
class ShortUrlAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\ShortUrlAnnotator',
			new ShortUrlAnnotator( $semanticData, $configuration )
		);
	}

}
