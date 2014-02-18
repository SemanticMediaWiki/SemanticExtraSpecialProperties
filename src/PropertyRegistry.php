<?php

namespace SESP;

use SMW\DataTypeRegistry;
use SMW\DIProperty;
use SMWDataItem as DataItem;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 * @author mwjames
 */
class PropertyRegistry {

	/** @var PropertyRegistry */
	protected static $instance = null;

	/**
	 * @since 0.3
	 *
	 * @return PropertyRegistry
	 */
	public static function getInstance() {

		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @since 0.3
	 */
	public static function clear() {
		self::$instance = null;
	}

	/**
	 * @since 0.3
	 *
	 * @param string $id
	 *
	 * @return string|null
	 */
	public function getPropertyId( $id ) {

		$container = array(
			'_CUSER'  => '___CUSER',
			'_REVID'  => '___REVID',
			'_PAGEID' => '___PAGEID',
			'_VIEWS'  => '___VIEWS',
			'_SUBP'   => '___SUBP',
			'_NREV'   => '___NREV',
			'_NTREV'  => '___NTREV',
			'_EUSER'  => '___EUSER',
			'_SHORTURL'  => '___SHORTURL',
			'_METADATA'  => '___EXIFDATETIME',
			'_METADATA'  => '___EXIFSOFTWARE',
			'_USERREG'   => '___USERREG',
			'_MIMETYPE'  => '___MIMETYPE',
			'_MEDIATYPE' => '___MEDIATYPE',
			'_EXIFDATETIME'   => '___EXIFDATETIME',
			'_EXIFSOFTWARE'   => '___EXIFSOFTWARE',
			'_EXIFDATA'  => '___EXIFDATA' // see git.wikimedia.org 0.2.8 master
		);

		if ( isset( $container[$id] ) ) {
			return $container[$id];
		}

		return null;
	}

	/**
	 * @since 0.3
	 *
	 * @param array $propertyTableDefinitions
	 * @param array $configuration
	 *
	 * @return boolean
	 */
	public function registerAsFixedTables( &$propertyTableDefinitions, $configuration ) {

		if ( !isset( $configuration['sespUseAsFixedTables'] ) || !$configuration['sespUseAsFixedTables'] ) {
			return true;
		}

		$enabledSpecialProperties = array_flip( $configuration['sespSpecialProperties'] );

		foreach( $this->getPropertyTypeList() as $externalId => $dataItemType ) {

			if ( !isset( $enabledSpecialProperties[$externalId] ) ) {
				continue;
			}

			$tableName = 'smw_ftp_sesp' . strtolower( $externalId );

			$propertyTableDefinitions[$tableName] = new \SMW\SQLStore\TableDefinition(
				$dataItemType,
				$tableName,
				$this->getPropertyId( $externalId )
			);
		}

		// Probably we don't want to create any exif-specific fixed tables besides
		// the '_EXIFDATA'

		return true;
	}

	/**
	 * @since 0.3
	 */
	public function registerPropertiesAndAliases() {

		foreach ( $this->getPropertyTypeList() as $externalId => $type ) {

			$id = $this->getPropertyId( $externalId );

			DIProperty::registerProperty(
				$id,
				DataTypeRegistry::getInstance()->getDefaultDataItemTypeId( $type ),
				$this->getPropertyLabel( $externalId )
			);

			DIProperty::registerPropertyAlias(
				$id,
				$this->getPropertyAlias( $externalId )
			);
		}

		// If there are more exif data, those should only registered if
		// '_EXIFDATA' is used as configuration parameter

		return true;
	}

	protected function getPropertyLabel( $id ) {

		$labels = array(
			'_REVID'  => 'sesp-property-revision-id',
			'_PAGEID' => 'sesp-property-page-id',
			'_EUSER'  => 'sesp-property-author',
			'_CUSER'  => 'sesp-property-first-author',
			'_VIEWS'  => 'sesp-property-view-count',
			'_SUBP'   => 'sesp-property-subpages',
			'_NREV'   => 'sesp-property-revisions',
			'_NTREV'  => 'sesp-property-talk-revisions',
			'_MIMETYPE'  => 'sesp-property-mimetype',
			'_MEDIATYPE' => 'sesp-property-mediatype',
			'_SHORTURL'  => 'sesp-property-shorturl',
			'_USERREG'   => 'sesp-property-user-registration-date',
			'_EXIFDATETIME' => 'exif-datetimeoriginal',
			'_EXIFSOFTWARE' => 'exif-software',
			'_EXIFDATA' => 'sesp-property-exif-data'
		);

		if ( isset( $labels[$id] ) ) {
			return wfMessage( $labels[$id] )->inContentLanguage()->text();
		}

		return null;
	}

	protected function getPropertyAlias( $id ) {

		$aliases = array(
			'_REVID'  => 'Revision ID',
			'_PAGEID' => 'Page Id',
			'_EUSER'  => 'Page author',
			'_CUSER'  => 'Page creator',
			'_VIEWS'  => 'Number of page views',
			'_SUBP'   => 'Subpage',
			'_NREV'   => 'Number of revisions',
			'_NTREV'  => 'Number of talk page revisions',
			'_MIMETYPE'  => 'MIME type',
			'_MEDIATYPE' => 'Media type',
			'_SHORTURL'  => 'Short URL',
			'_USERREG'   =>  'User registration date',
			'_EXIFDATETIME' => 'Exposure date',
			'_EXIFSOFTWARE' => 'Software',
			'_EXIFDATA' => 'Exif dataa'
		);

		if ( isset( $aliases[$id] ) ) {
			return $aliases[$id];
		}

		return null;
	}

	protected function getPropertyTypeList() {
		return array(
			'_REVID'  => DataItem::TYPE_NUMBER,
			'_PAGEID' => DataItem::TYPE_NUMBER,
			'_EUSER'  => DataItem::TYPE_WIKIPAGE,
			'_CUSER'  => DataItem::TYPE_WIKIPAGE,
			'_VIEWS'  => DataItem::TYPE_NUMBER,
			'_SUBP'   => DataItem::TYPE_WIKIPAGE,
			'_NREV'   => DataItem::TYPE_NUMBER,
			'_NTREV'  => DataItem::TYPE_NUMBER,
			'_MIMETYPE'  => DataItem::TYPE_BLOB,
			'_MEDIATYPE' => DataItem::TYPE_BLOB,
			'_SHORTURL'  => DataItem::TYPE_URI,
			'_USERREG'   => DataItem::TYPE_TIME,
			'_EXIFDATETIME' => DataItem::TYPE_TIME,
			'_EXIFSOFTWARE' => DataItem::TYPE_BLOB,
			'_EXIFDATA' => DataItem::TYPE_WIKIPAGE,
		);
	}

}
