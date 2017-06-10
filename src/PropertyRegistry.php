<?php

namespace SESP;

use SMW\PropertyRegistry as BasePropertyRegistry;
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

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @since 2.0
	 *
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
	}

	/**
	 * @since 1.0
	 *
	 * @param PropertyRegistry $propertyRegistry
	 *
	 * @return boolean
	 */
	public function registerOn( BasePropertyRegistry $propertyRegistry ) {

		$propertyDefinitions = $this->appFactory->getPropertyDefinitions();
		$labels = $propertyDefinitions->getLabels();

		foreach ( $propertyDefinitions as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$this->addPropertyDefinition( $propertyRegistry, $propertyDefinitions, $definition, $labels );
		}

		foreach ( $propertyDefinitions->safeGet( '_EXIF', [] ) as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$this->addPropertyDefinition( $propertyRegistry, $propertyDefinitions, $definition, $labels );
		}

		return true;
	}

	/**
	 * @since 2.0
	 *
	 * @param array $customFixedProperties
	 * @param array $fixedPropertyTablePrefix
	 */
	public function registerAsFixedProperties( &$customFixedProperties, &$fixedPropertyTablePrefix ) {

		if ( $this->appFactory->getOption( 'sespUseAsFixedTables' ) === false ) {
			return;
		}

		$propertyDefinitions = $this->appFactory->getPropertyDefinitions();

		$properties = array_flip(
			$this->appFactory->getOption( 'sespSpecialProperties', [] )
		);

		foreach ( $propertyDefinitions as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$id = $definition['id'];

			if ( isset( $properties[$key] ) ) {
				$customFixedProperties[$id] = str_replace( [ '___', '__' ], '_', strtolower( $id ) );

				// Legacy setting `smw_ftp` vs. `smw_fpt`
				$fixedPropertyTablePrefix[$id] = 'smw_ftp_sesp';
			}
		}
	}

	private function addPropertyDefinition( $propertyRegistry, $propertyDefinitions, $definition, $aliases ) {

		$visible = isset( $definition['show'] ) ? $definition['show'] : false;
		$annotable = false;

		// If someone screws up the definition format we just fail epically here
		// on purpose

		$propertyRegistry->registerProperty(
			$definition['id'],
			$definition['type'],
			$definition['label'],
			$visible,
			$annotable
		);

		$alias = isset( $definition['alias'] ) ? $definition['alias'] : 'smw-unknown-alias';
		$label = isset( $aliases[$definition['id']] ) ? $aliases[$definition['id']] : $propertyDefinitions->getLabel( $alias );

		$propertyRegistry->registerPropertyAlias(
			$definition['id'],
			$label
		);

		$propertyRegistry->registerPropertyAliasByMsgKey(
			$definition['id'],
			$alias
		);

		$desc = isset( $definition['desc'] ) ? $definition['desc'] : '';

		$propertyRegistry->registerPropertyDescriptionMsgKeyById(
			$definition['id'],
			$desc
		);
	}

}
