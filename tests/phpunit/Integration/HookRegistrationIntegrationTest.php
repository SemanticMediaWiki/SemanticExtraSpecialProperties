<?php

namespace SESP\Tests\Integration;

use SESP\Setup;
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
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistrationIntegrationTest extends \PHPUnit_Framework_TestCase {

	protected $sespCacheType = null;
	protected $wgHooks = array();
	protected $wgExtensionFunctions = array();
	protected $hookRegistrationStatus = array();

	protected function setUp() {
		parent::setUp();

		// Clears all GLOBALS that can influence the test run
		$this->wgHooks = $GLOBALS['wgHooks'];
		$this->wgExtensionFunctions = $GLOBALS['wgExtensionFunctions'];
		$this->sespCacheType = $GLOBALS['sespCacheType'];

		// Set default values used during the test run
		$GLOBALS['wgHooks'] = array();
		$GLOBALS['wgExtensionFunctions'] = array();
		$GLOBALS['sespCacheType'] = 'hash';

		$setup = new Setup( $GLOBALS, __DIR__ . '/../../../', array( $this, 'reportHookRegistrationStatus' ) );
		$setup->run();

		call_user_func( $GLOBALS['wgExtensionFunctions']['semantic-extra-special-properties'] );
	}

	protected function tearDown() {
		$GLOBALS['sespCacheType'] = $this->sespCacheType;
		$GLOBALS['wgHooks'] = $this->wgHooks;
		$GLOBALS['wgExtensionFunctions'] = $this->wgExtensionFunctions;

		parent::tearDown();
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

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );

		$this->assertEquals(
			'_num',
			DIProperty::getPredefinedPropertyTypeId( $propertyId )
		);

		// FIXME
		// This can't be tested because of scope creep in DIProperty where the
		// static instance can't be reset in order to verify that the hook has
		// been executed with a different condition
		// $this->assertArrayHasKey( 'smwInitProperties', $this->hookRegistrationStatus );
	}

	/**
	 * @depends testExtensionHookRegistration
	 */
	public function testStoreUpdateHookInterfaceInitialization() {

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		StoreFactory::clear();
		StoreFactory::getStore()->updateData( $semanticData );

		$this->assertTrue( true );

		$this->assertArrayHasKey( 'SMWStore::updateDataBefore', $this->hookRegistrationStatus );
		$this->assertArrayHasKey( 'SMW::SQLStore::updatePropertyTableDefinitions', $this->hookRegistrationStatus );
	}

	public function reportHookRegistrationStatus( $key, $status ) {
		$this->hookRegistrationStatus[ $key ] = $status;
	}

}
