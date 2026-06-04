<?php

namespace SESP\Tests\Integration;

use SMW\Schema\SchemaFactory;

/**
 * @group semantic-extra-special-properties
 * @group medium
 *
 * @license GPL-2.0-or-later
 * @since 7.0.0
 */
class GroupSchemaFileIntegrityTest extends \PHPUnit\Framework\TestCase {

	/**
	 * Every property-group schema file referenced by the import manifest must
	 * pass Semantic MediaWiki's `PROPERTY_GROUP_SCHEMA` validation. An invalid
	 * file is silently skipped at import time, so the groups would never appear
	 * on Special:Browse without this guard catching it first.
	 *
	 * @dataProvider groupSchemaFileProvider
	 */
	public function testGroupSchemaFileIsValid( string $name, string $file ) {
		$contents = file_get_contents( $file );

		$this->assertIsString( $contents );

		$schemaFactory = new SchemaFactory();
		$schema = $schemaFactory->newSchema( $name, $contents );

		$this->assertSame(
			[],
			$schemaFactory->newSchemaValidator()->validate( $schema ),
			"$file is not a valid PROPERTY_GROUP_SCHEMA"
		);
	}

	/**
	 * Guards the guard: the pre-3.2 array shape (`group_name`/`properties`) must
	 * be rejected, otherwise a regression to it would slip past
	 * testGroupSchemaFileIsValid unnoticed.
	 */
	public function testLegacyArrayShapeIsRejected() {
		$legacy = json_encode( [
			'type' => 'PROPERTY_GROUP_SCHEMA',
			'groups' => [
				[
					'group_name' => 'Legacy',
					'properties' => [ '___PAGEID' ],
				],
			],
		] );

		$schemaFactory = new SchemaFactory();
		$schema = $schemaFactory->newSchema( 'Legacy', $legacy );

		$this->assertNotSame(
			[],
			$schemaFactory->newSchemaValidator()->validate( $schema ),
			'the legacy array-shaped groups must fail validation'
		);
	}

	public function groupSchemaFileProvider() {
		$importDir = __DIR__ . '/../../../data/import';
		$manifest = json_decode( file_get_contents( "$importDir/sesp.groups.json" ), true );

		$provider = [];

		foreach ( $manifest['import'] as $entry ) {
			$page = $entry['page'];
			$provider[$page] = [ $page, $importDir . $entry['contents']['importFrom'] ];
		}

		return $provider;
	}

}
