<?php

namespace SESP;

use Onoi\Cache\Cache;
use Onoi\Cache\NullCache;
use SMW\Message;
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
	 * @var LabelFetcher
	 */
	private $labelFetcher;

	/**
	 * @var string
	 */
	private $propertyDefinitionFile;

	/**
	 * @var string
	 */
	private $labelCacheVersion = 0;

	/**
	 * @var array|null
	 */
	private $propertyDefinitions;

	/**
	 * @var array
	 */
	private $localPropertyDefinitions = [];

	/**
	 * @since 2.0
	 *
	 * @param LabelFetcher $labelFetcher
	 * @param string $propertyDefinitionFile
	 */
	public function __construct( LabelFetcher $labelFetcher, $propertyDefinitionFile = '' ) {
		$this->labelFetcher = $labelFetcher;
		$this->propertyDefinitionFile = $propertyDefinitionFile;

		if ( $this->propertyDefinitionFile === '' ) {
			$this->propertyDefinitionFile = $GLOBALS['sespgDefinitionsFile'];
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
	 * @since 2.0
	 *
	 * @return array
	 */
	public function getLabels() {

		if ( $this->propertyDefinitions === null ) {
			$this->initPropertyDefinitions();
		}

		return $this->labelFetcher->getLabelsFrom( $this );
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public function getLabel( $key ) {
		return $this->labelFetcher->getLabel( $key );
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
			str_replace( [ '\\', '/' ], DIRECTORY_SEPARATOR, $this->propertyDefinitionFile )
		);

		$this->propertyDefinitions = json_decode(
			$contents,
			true
		);

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$this->propertyDefinitions = [];
		}

		$this->propertyDefinitions += $this->localPropertyDefinitions;
	}

}
