<?php

namespace SESP\Tests\Integration;

use SMW\Tests\Utils\UtilityFactory;

/**
 * @group semantic-extra-special-properties
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 1.3
 *
 * @author mwjames
 */
class I18nJsonFileIntegrityTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @dataProvider i18nFileProvider
	 */
	public function testI18NJsonDecodeEncode( string $file ) {
		$jsonFileReader = UtilityFactory::getInstance()->newJsonFileReader( $file );

		$this->assertIsInt(
			$jsonFileReader->getModificationTime()
		);

		$this->assertIsArray(
			$jsonFileReader->read()
		);
	}

	public function i18nFileProvider() {
		$provider = [];
		$location = $GLOBALS['wgMessagesDirs']['SemanticExtraSpecialProperties'];

		$bulkFileProvider = UtilityFactory::getInstance()->newBulkFileProvider( array_pop( $location ) );
		$bulkFileProvider->searchByFileExtension( 'json' );

		foreach ( $bulkFileProvider->getFiles() as $file ) {
			$provider[] = [ $file ];
		}

		return $provider;
	}

}
