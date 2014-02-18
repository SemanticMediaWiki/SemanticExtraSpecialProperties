<?php

namespace SESP\Tests;

use SESP\ExifDataAnnotator;

/**
 * @covers \SESP\ExifDataAnnotator
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
class ExifDataAnnotatorTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$semanticData = $this->getMockBuilder( '\SMW\SemanticData' )
			->disableOriginalConstructor()
			->getMock();

		$configuration = array();

		$this->assertInstanceOf(
			'\SESP\ExifDataAnnotator',
			new ExifDataAnnotator( $semanticData, $configuration )
		);
	}

}
