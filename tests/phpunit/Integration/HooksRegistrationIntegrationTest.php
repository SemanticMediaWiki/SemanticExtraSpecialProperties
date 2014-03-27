<?php

namespace SESP\Tests\Integration;

use SESP\PropertyRegistry;
use SESP\Setup;

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
class HooksRegistrationIntegrationTest extends \PHPUnit_Framework_TestCase {

	protected $wgHooks = array();
	protected $wgExtensionFunctions = array();
	protected $hookInvokeStatus = null;

	protected function setUp() {

		// Clears all pre-set hooks, the Setup routine will register only those
		// hooks that are relevant to SWL so that tests are executed without
		// influence of other extensions that use the same hook
		$this->wgHooks = $GLOBALS['wgHooks'];
		$this->wgExtensionFunctions = $GLOBALS['wgExtensionFunctions'];

		$GLOBALS['wgHooks'] = array();
		$GLOBALS['wgExtensionFunctions'] = array();
		Setup::getInstance()->setGlobalVars( $GLOBALS )->run();

		parent::setUp();
	}

	protected function tearDown() {
		parent::tearDown();

		$GLOBALS['wgHooks'] = $this->wgHooks;
		$GLOBALS['wgExtensionFunctions'] = $this->wgExtensionFunctions;
	}

	public function testExtensionHookRegistration() {

		$registry = $GLOBALS['wgExtensionFunctions']['semantic-extra-special-properties'];

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

		$this->assertArrayHasKey( 'smwInitProperties', $this->hookInvokeStatus );
	}

	/**
	 * @depends testExtensionHookRegistration
	 */
	public function testStoreUpdateDataBeforeHook() {

		$this->callExtensionFunctions();

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		StoreFactory::clear();
		StoreFactory::getStore()->updateData( $semanticData );

		$this->assertArrayHasKey( 'SMWStore::updateDataBefore', $this->hookInvokeStatus );
		$this->assertArrayHasKey( 'SMW::SQLStore::updatePropertyTableDefinitions', $this->hookInvokeStatus );
	}

	protected function callExtensionFunctions() {
		call_user_func_array(
			$GLOBALS['wgExtensionFunctions']['semantic-extra-special-properties'],
			array( array( $this, 'reportHookInvokeStatus' ) )
		);
	}

	public function reportHookInvokeStatus( $key, $status ) {
		$this->hookInvokeStatus[ $key ] = $status;
	}

}
