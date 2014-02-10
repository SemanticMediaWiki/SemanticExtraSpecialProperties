<?php

namespace SESP\Tests;

use SMW\SemanticData;
use SMW\StoreFactory;
use SMw\DIWikiPage;

use Title;

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
class StoreIntegrationTest extends \PHPUnit_Framework_TestCase {

	public function testRunUpdateDataToVerifyHookInterfaceInitialization() {

		$semanticData = new SemanticData(
			DIWikiPage::newFromTitle( Title::newFromText( __METHOD__ ) )
		);

		StoreFactory::clear();
		StoreFactory::getStore()->updateData( $semanticData );

		$this->assertTrue( true );
	}

}
