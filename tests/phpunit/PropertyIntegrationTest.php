<?php

namespace SESP\Tests;

use SESP\PropertyRegistry;
use SMW\DIProperty;

/**
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
class PropertyIntegrationTest extends \PHPUnit_Framework_TestCase {

	public function testDIPropertyToFetchPropertyTypeId() {

		$propertyId = PropertyRegistry::getInstance()->getPropertyId( '_REVID' );

		$this->assertEquals(
			'_num',
			DIProperty::getPredefinedPropertyTypeId( $propertyId )
		);
	}

}
