<?php

namespace SESP;

use IteratorAggregate;
use ArrayIterator;
use InvalidArgumentException;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class PropertyDefinitions implements IteratorAggregate {

	/**
	 * @var string
	 */
	private $propertyDefinitionFile;

	/**
	 * @var array|null
	 */
	private $propertyDefinitions;

	/**
	 * @var array
	 */
	private $localPropertyDefinitions = array();

	/**
	 * @since 2.0
	 *
	 * @param string $propertyDefinitionFile
	 */
	public function __construct( $propertyDefinitionFile = '' ) {
		$this->propertyDefinitionFile = $propertyDefinitionFile;

		if ( $this->propertyDefinitionFile === '' ) {
			$this->propertyDefinitionFile = $GLOBALS['sespPropertyDefinitionFile'];
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param array $localPropertyDefinitions
	 */
	public function setLocalPropertyDefinitions( array $localPropertyDefinitions ) {
		$this->localPropertyDefinitions = $localPropertyDefinitions;
	}

	/**
	 * @since 2.0
	 *
	 * @param array $propertyDefinitions
	 */
	public function setPropertyDefinitions( array $propertyDefinitions ) {
		$this->propertyDefinitions = $propertyDefinitions;
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function isLocalDef( $key ) {
		return isset( $this->localPropertyDefinitions[$key] ) || array_key_exists( $key, $this->localPropertyDefinitions );
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function has( $key ) {
		return isset( $this->propertyDefinitions[$key] ) || array_key_exists( $key, $this->propertyDefinitions );
	}

	/**
	 * @since 2.3
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function get( $key ) {

		if ( $this->has( $key ) ) {
			return $this->propertyDefinitions[$key];
		}

		throw new InvalidArgumentException( "{$key} is an unregistered option" );
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return boolean
	 */
	public function deepHas( $key, $key2 ) {
		return isset( $this->propertyDefinitions[$key][$key2] );
	}

	/**
	 * @since 2.3
	 *
	 * @param string $key
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function deepGet( $key, $key2 ) {

		if ( $this->deepHas( $key, $key2 ) ) {
			return $this->propertyDefinitions[$key][$key2];
		}

		throw new InvalidArgumentException( "{$key}{$key2} is an unregistered option" );
	}

	/**
	 * @since 3.0
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function safeGet( $key, $default = false ) {
		return $this->has( $key ) ? $this->propertyDefinitions[$key] : $default;
	}

	/**
	 * @see IteratorAggregate::getIterator
	 *
	 * @since 2.0
	 *
	 * @return Iterator
	 */
	public function getIterator() {

		if ( $this->propertyDefinitions === null ) {
			$this->initPropertyDefinitions();
		}

		return new ArrayIterator( $this->propertyDefinitions );
	}

	private function initPropertyDefinitions() {

		$contents = file_get_contents(
			str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $this->propertyDefinitionFile )
		);

		$this->propertyDefinitions = json_decode(
			$contents,
			true
		);

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->propertyDefinitions = array();
		}

		$this->propertyDefinitions += $this->localPropertyDefinitions;
	}

}
