<?php

namespace SESP\Tests\Integration;

use SMW\Tests\TestEnvironment;

/**
 * @group semantic-extra-special-properties
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class DefinitionJsonFileIntegrityTest extends \PHPUnit_Framework_TestCase {

	public function testDecodeEncode() {
		$testEnvironment = new TestEnvironment();

		$jsonFileReader = $testEnvironment->getUtilityFactory()->newJsonFileReader(
			$GLOBALS['sespgDefinitionsFile']
		);

		$this->assertIsInt(
			$jsonFileReader->getModificationTime()
		);

		$this->assertIsArray(
			$jsonFileReader->read()
		);
	}

}
