<?php

namespace SESP\Tests\Definition;

use SESP\Definition\DefinitionReader;

/**
 * @covers \SESP\Definition\DefinitionReader
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
class DefinitionReaderTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {
		$this->assertInstanceOf(
			'\SESP\Definition\DefinitionReader',
			new DefinitionReader
		);
	}

	public function testDefaultDefinitionFileAvailability() {
		$instance = new DefinitionReader;
		$this->assertInternalType( 'array', $instance->getDefinitions() );
	}

	public function testGetModificationTime() {
		$instance = new DefinitionReader;
		$this->assertInternalType( 'integer', $instance->getModificationTime() );
	}

	public function testInaccessibleJsonFileThrowsExeception() {

		$this->setExpectedException( 'RuntimeException' );

		$instance = new DefinitionReader( 'foo' );
		$instance->getDefinitions();
	}

	public function testMalformedJsonFileThrowsException() {

		$this->setExpectedException( 'UnexpectedValueException' );

		$instance = new DefinitionReader( __DIR__ . '/../Fixture/malformed.json' );
		$instance->getDefinitions();
	}

}
