<?php

namespace SESP\Tests\Integration;

use SESP\PropertyRegistry;
use SMW\SemanticData;
use SMW\StoreFactory;
use SMW\DIWikiPage;
use SMW\DIProperty;

use Title;

/**
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @licence GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistrationIntegrationTest extends \PHPUnit_Framework_TestCase {

	protected $sespCacheType = null;
	protected $wgHooks = array();
	protected $wgExtensionFunctions = array();

	protected function setUp() {

		// Clears all GLOBALS that can influence the test run
		$this->wgHooks = $GLOBALS['wgHooks'];
		$this->wgExtensionFunctions = $GLOBALS['wgExtensionFunctions'];
		$this->sespCacheType = $GLOBALS['sespCacheType'];

		// Set default values used during the test run
		$GLOBALS['wgHooks'] = array();
		$GLOBALS['wgExtensionFunctions'] = array();
		$GLOBALS['sespCacheType'] = 'hash';

		parent::setUp();
	}

	protected function tearDown() {
		parent::tearDown();

		$GLOBALS['sespCacheType'] = $this->sespCacheType;
		$GLOBALS['wgHooks'] = $this->wgHooks;
		$GLOBALS['wgExtensionFunctions'] = $this->wgExtensionFunctions;
	}

	public function testExtensionHookRegistration() {

		$registry = $this->wgExtensionFunctions['semantic-extra-special-properties'];

		$this->assertTrue( is_callable( $registry ) );
		$this->assertTrue( call_user_func( $registry) );
	}

	/**
	 * @depends testExtensionHookRegistration
	 */
	public function testInitPropertiesHookToFetchPropertyTypeId() {

		$this->callExtensionFunctions();

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );

		$this->assertEquals(
			'_num',
			DIProperty::getPredefinedPropertyTypeId( $propertyId )
		);
	}

	/**
	 * @depends testExtensionHookRegistration
	 */
	public function testStoreUpdateHookInterfaceInitialization() {

		$this->callExtensionFunctions();

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		StoreFactory::clear();
		StoreFactory::getStore()->updateData( $semanticData );

		$this->assertTrue( true );
	}

	protected function callExtensionFunctions() {
		call_user_func_array(
			$this->wgExtensionFunctions['semantic-extra-special-properties'],
			array()
		);
	}

}
