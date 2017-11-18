<?php

namespace SESP\Tests\Integration;

use SMW\Tests\TestEnvironment;

/**
 * @group semantic-extra-special-properties
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class DefinitionJsonFileIntegrityTest extends \PHPUnit_Framework_TestCase {

	public function testDecodeEncode() {

		$testEnvironment = new TestEnvironment();

		$jsonFileReader = $testEnvironment->getUtilityFactory()->newJsonFileReader(
			$GLOBALS['sespgDefinitionFile']
		);

		$this->assertInternalType(
			'integer',
			$jsonFileReader->getModificationTime()
		);

		$this->assertInternalType(
			'array',
			$jsonFileReader->read()
		);
	}

}
