<?php

namespace SESP\Tests;

use SESP\Setup;

/**
 * @uses \SESP\Setup
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SetupTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$globalsVars = array();
		$directory   = '';

		$this->assertInstanceOf(
			'\SESP\Setup',
			new Setup( $globalsVars, $directory )
		);
	}

	public function testRun() {

		$globalsVars = array();
		$directory   = '';

		$instance = new Setup( $globalsVars, $directory );
		$instance->run();

		$this->assertArrayHasKey( 'sespCacheType', $globalsVars );
		$this->assertArrayHasKey( 'wgMessagesDirs', $globalsVars );
		$this->assertArrayHasKey( 'wgExtensionMessagesFiles', $globalsVars );
		$this->assertArrayHasKey( 'wgExtensionFunctions', $globalsVars );
	}

	/**
	 * @depends testRun
	 */
	public function testExtensionFunctions() {

		$globalsVars = array();
		$directory   = '';

		$instance = new Setup( $globalsVars, $directory );
		$instance->run();

		$wgExtensionFunctions = $globalsVars['wgExtensionFunctions'];

		$this->assertArrayHasKey( 'semantic-extra-special-properties', $wgExtensionFunctions );

		$this->assertInstanceOf(
			'\Closure',
			$wgExtensionFunctions[ 'semantic-extra-special-properties' ]
		);
	}

}
