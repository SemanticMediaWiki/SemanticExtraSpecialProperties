<?php

namespace SESP;

use SMW\PropertyRegistry as BasePropertyRegistry;
use SMW\DataTypeRegistry;
use SMW\DIProperty;
use SMW\Message;
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

		$definitions = $this->appFactory->getPropertyDefinitions();

		foreach ( $definitions as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$this->addPropertyDefinition( $propertyRegistry, $definition );
		}

		foreach ( $definitions->safeGet( '_EXIF', array() ) as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$this->addPropertyDefinition( $propertyRegistry, $definition );
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

		$definitions = $this->appFactory->getPropertyDefinitions();

		$properties = array_flip(
			$this->appFactory->getOption( 'sespSpecialProperties', array() )
		);

		foreach ( $definitions as $key => $definition ) {

			if ( !isset( $definition['id'] ) ) {
				continue;
			}

			$id = $definition['id'];

			if ( isset( $properties[$key] ) ) {
				$customFixedProperties[$id] = str_replace( array( '___', '__' ), '_', strtolower( $id ) );

				// Legacy setting `smw_ftp` vs. `smw_fpt`
				$fixedPropertyTablePrefix[$id] = 'smw_ftp_sesp';
			}
		}
	}

	private function addPropertyDefinition( $propertyRegistry, $definition ) {

		$visible = isset( $definition['show'] ) ? $definition['show'] : false;
		$annotable = false;

		$alias = isset( $definition['alias'] ) ? $definition['alias'] : 'smw-unknown-alias';

		// If someone screws up the definition format we just fail epically here
		// on purpose

		$propertyRegistry->registerProperty(
			$definition['id'],
			$definition['type'],
			$definition['label'],
			$visible,
			$annotable
		);

		$propertyRegistry->registerPropertyAlias(
			$definition['id'],
			Message::get( $alias )
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
