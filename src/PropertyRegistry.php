<?php

namespace SESP;

use SESP\Definition\DefinitionReader;
use SESP\Cache\MessageCache;

use SMW\DataTypeRegistry;
use SMW\DIProperty;
use SMWDataItem as DataItem;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyRegistry {

	/** @var PropertyRegistry */
	protected static $instance = null;

	/** @var MessageCache */
	protected $messageCache = null;

	protected $definitions = null;

	/**
	 * @since 1.2.0
	 *
	 * @param DefinitionReader $definitionReader
	 * @param MessageCache $messageCache
	 */
	protected function __construct( DefinitionReader $definitionReader, MessageCache $messageCache ) {
		$this->definitions = $definitionReader->getDefinitions();
		$this->messageCache = $messageCache;

		$this->messageCache->setCacheTimeOffset( $definitionReader->getModificationTime() );
	}

	/**
	 * @since 1.0
	 *
	 * @return PropertyRegistry
	 */
	public static function getInstance() {

		if ( self::$instance === null ) {
			self::$instance = new self(
				new DefinitionReader,
				new MessageCache()
			);
		}

		return self::$instance;
	}

	/**
	 * @since 1.0
	 */
	public static function clear() {
		self::$instance = null;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $id
	 *
	 * @return string|null
	 */
	public function getPropertyId( $id ) {
		return $this->lookupWithIndexForId( 'id', $id );
	}

	/**
	 * @since 1.0
	 *
	 * @param string $id
	 *
	 * @return string|null
	 */
	public function getPropertyType( $id ) {
		return $this->lookupWithIndexForId( 'type', $id );
	}

	/**
	 * Only properties that are customized are also considered as possible
	 * candidates for a fixed table
	 *
	 * @note Specific exif properties are not considered as fixed table entry
	 *
	 * @since 1.0
	 *
	 * @param array $customFixedProperties
	 * @param array $configuration
	 *
	 * @return boolean
	 */
	public function registerAsFixedTables( &$customFixedProperties, $configuration ) {

		if ( !isset( $configuration['sespUseAsFixedTables'] ) || !$configuration['sespUseAsFixedTables'] ) {
			return true;
		}

		$enabledSpecialProperties = array_flip( $configuration['sespSpecialProperties'] );
		$propertyTypeList = array_keys( $this->definitions );

		foreach( $propertyTypeList as $externalId ) {

			$dataItemType = $this->getPropertyType( $externalId );

			if ( !isset( $enabledSpecialProperties[ $externalId ] ) || $dataItemType === null ) {
				continue;
			}

			$customFixedProperties[$externalId] = str_replace( '__', '_', '_sesp' . strtolower( $externalId ) );
		}

		return true;
	}

	/**
	 * @note If there are an exceedingly amount of possible exif properties, those
	 * should only registered if '_EXIFDATA' is used as configuration parameter
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function registerPropertiesAndAliases() {

		$this->registerPropertiesFromList(
			array_keys( $this->definitions )
		);

		$this->registerPropertiesFromList(
			array_keys( $this->definitions['_EXIF'] )
		);

		return true;
	}

	protected function registerPropertiesFromList( array $propertyList ) {

		foreach ( $propertyList as $externalId ) {

			$propertyId = $this->getPropertyId( $externalId );

			if ( $propertyId === null ) {
				continue;
			}

			DIProperty::registerProperty(
				$propertyId,
				$this->getPropertyDataItemTypeId( $externalId ),
				$this->getPropertyLabel( $externalId ),
				$this->getPropertyVisibility( $externalId )
			);

			DIProperty::registerPropertyAlias(
				$propertyId,
				$this->getPropertyAlias( $externalId )
			);
		}
	}

	protected function getPropertyLabel( $id ) {
		return $this->lookupWithIndexForId( 'label', $id );
	}

	protected function getPropertyVisibility( $id ) {

		$show = $this->lookupWithIndexForId( 'show', $id );

		if ( $show === null ) {
			return false;
		}

		return $show;
	}

	protected function getPropertyAlias( $id ) {

		$msgkey = $this->lookupWithIndexForId( 'alias', $id );

		if ( $msgkey ) {
			return $this->messageCache->inUserLanguage()->get( $msgkey );
		}

		return false;
	}

	protected function getPropertyDataItemTypeId( $id ) {

		$type = $this->getPropertyType( $id );

		if ( $type ) {
			return DataTypeRegistry::getInstance()->getDefaultDataItemTypeId( $type );
		}

		return null;
	}

	protected function lookupWithIndexForId( $index, $id ) {

		$id = strtoupper( $id );

		if ( isset( $this->definitions[ $id ] ) && isset( $this->definitions[ $id ][ $index ] ) ) {
			return $this->definitions[ $id ][ $index ];
		}

		if ( isset( $this->definitions['_EXIF'][ $id ] ) && isset( $this->definitions['_EXIF'][ $id ][ $index ] ) ) {
			return $this->definitions['_EXIF'][ $id ][ $index ];
		}

		return null;
	}

}
