<?php

namespace SESP\Definition;

use RuntimeException;
use UnexpectedValueException;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 1.1.1
 *
 * @author mwjames
 */
class DefinitionReader {

	protected $definitionFile = null;
	protected $definitions = null;

	/**
	 * @since 1.1.1
	 *
	 * @param string|null $definitionFile
	 */
	public function __construct( $definitionFile = null ) {
		$this->definitionFile = $definitionFile;
	}

	/**
	 * @since 1.1.1
	 *
	 * @return array
	 * @throws RuntimeException
	 * @throws UnexpectedValueException
	 */
	public function getDefinitions() {

		if ( $this->definitionFile === null ) {
			$this->definitionFile = $this->getDefaultDefinitionFile();
		}

		if ( $this->definitions === null ) {
			$this->definitions = $this->decodeJsonFile( $this->isReadableOrThrowException( $this->definitionFile ) );
		}

		return $this->definitions;
	}

	/**
	 * @since 1.1.1
	 *
	 * @return integer
	 */
	public function getModificationTime() {
		return filemtime( $this->isReadableOrThrowException( $this->getDefaultDefinitionFile() ) );
	}

	protected function getDefaultDefinitionFile() {
		return __DIR__ . '/' . 'definitions.json';
	}

	protected function isReadableOrThrowException( $definitionFile ) {

		$definitionFile = str_replace( array( '\\', '/' ), DIRECTORY_SEPARATOR, $definitionFile );

		if ( is_readable( $definitionFile ) ) {
			return $definitionFile;
		}

		throw new RuntimeException( "Expected a {$definitionFile} file" );
	}

	protected function decodeJsonFile( $file ) {

		$definitions = json_decode( file_get_contents( $file ), true );

		if ( $definitions !== null && is_array( $definitions ) && json_last_error() === JSON_ERROR_NONE ) {
			return $definitions;
		}

		throw new UnexpectedValueException( 'Expected a JSON compatible format' );
	}

}
