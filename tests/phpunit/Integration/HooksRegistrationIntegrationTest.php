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
class HooksRegistrationIntegrationTest extends \PHPUnit_Framework_TestCase {

	public function testExtensionHookRegistration() {

		$registry = $GLOBALS['wgExtensionFunctions']['semantic-extra-special-properties'];

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
	}

}
