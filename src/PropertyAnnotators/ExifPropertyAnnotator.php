<?php

namespace SESP\PropertyAnnotators;

use FormatMetadata;
use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWContainerSemanticData as ContainerSemanticData;
use SMWDataItem as DataItem;
use SMWDIBlob as DIBlob;
use SMWDIContainer as DIContainer;
use SMWDINumber as DINumber;
use SMWDITime as DITime;

/**
 * @private
 * @ingroup SESP
 *
 * @see http://www.exiv2.org/tags.html
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 * @author rotsee
 * @author Stephan Gambke
 */
class ExifPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property ID
	 */
	public const PROP_ID = '___EXIFDATA';

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
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		$subject = $semanticData->getSubject();
		$title = $subject->getTitle();

		if ( !$title->inNamespace( NS_FILE ) ) {
			return;
		}

		$page = $this->appFactory->newWikiPage( $title );
		$file = $page->getFile();

		if ( !$file->exists() ) {
			return;
		}

		// #66
		$meta = $file->getMetadata();

		if ( !$meta ) {
			return false;
		}

		// Guard against "Error at offset 0 of 1 bytes"
		$exif = @unserialize( $meta, [ 'allowed_classes' => false ] );

		if ( !is_array( $exif ) || count( $exif ) === 0 ) {
			return;
		}

		if ( ( $width = $file->getWidth() ) !== null ) {
			$exif['ImageWidth'] = $width;
		}
		if ( ( $height = $file->getHeight() ) !== null ) {
			$exif['ImageLength'] = $height;
		}

		$dataItem = $this->getDataItemFromExifData( $subject, $exif );

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

	/**
	 * @since 2.0
	 *
	 * @param DIWikiPage $subject
	 * @param array $rawExif The raw EXIF data to be added.
	 */
	protected function getDataItemFromExifData( $subject, $rawExif ) {
		$containerSemanticData = $this->newContainerSemanticData(
			$subject
		);

		$this->addExifDataTo( $containerSemanticData, $rawExif );

		if ( $containerSemanticData->isEmpty() ) {
			return;
		}

		return new DIContainer( $containerSemanticData );
	}

	private function newContainerSemanticData( $subject ) {
		$subject = new DIWikiPage(
			$subject->getDBkey(),
			$subject->getNamespace(),
			$subject->getInterwiki(),
			'_EXIFDATA'
		);

		return new ContainerSemanticData( $subject );
	}

	private function addExifDataTo( $containerSemanticData, $rawExif ) {
		$exifDefinitions = $this->appFactory->getPropertyDefinitions()->safeGet( '_EXIF' );
		$formattedExif = FormatMetadata::getFormattedData( $rawExif );

		foreach ( $formattedExif as $key => $value ) {

			$dataItem = $this->createDataItemFromExif( $id, $key, $value, $rawExif, $exifDefinitions );

			if ( $dataItem instanceof DataItem ) {
				$containerSemanticData->addPropertyObjectValue( new DIProperty( $id ), $dataItem );
			}
		}
	}

	private function createDataItemFromExif( &$id, $key, $value, $rawExif, $exifDefinitions ) {
		$dataItem = null;
		$upKey = strtoupper( $key );

		if ( !isset( $exifDefinitions[$upKey] ) || !isset( $exifDefinitions[$upKey]['id'] ) ) {
			return;
		}

		$id = $exifDefinitions[$upKey]['id'];
		$type = $exifDefinitions[$upKey]['type'];

		switch ( $type ) {
			case '_num':
				$dataItem = is_numeric( $rawExif[$key] ) ? new DINumber( $rawExif[$key] ) : null;
				break;
			case '_txt':
				$dataItem = new DIBlob( $value );
				break;
			case '_dat':
				$dataItem = $this->makeDataItemTime( $rawExif[$key] );
		}

		return $dataItem;
	}

	private function makeDataItemTime( $exifValue ) {
		try {
			$datetime = $this->convertExifDate( $exifValue );
		} catch ( \Exception $e ) {
			$datetime = null;
		}

		if ( $datetime ) {
			return new DITime(
				DITime::CM_GREGORIAN,
				$datetime->format( 'Y' ),
				$datetime->format( 'n' ),
				$datetime->format( 'j' ),
				$datetime->format( 'G' ),
				$datetime->format( 'i' )
			);
		}
	}

	private function convertExifDate( $exifString ) {
		// Unknown date
		if ( $exifString == '0000:00:00 00:00:00' || $exifString == '    :  :     :  :  ' ) {
			return false;
		}

		// Full date
		if ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d) (?:\d\d):(?:\d\d):(?:\d\d)$/D', $exifString ) ) {
			return new \DateTime( $exifString );
		}

		// No second field, timeanddate doesn't include seconds but second still available in api
		if ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d) (?:\d\d):(?:\d\d)$/D', $exifString ) ) {
			return new \DateTime( $exifString . ':00' );
		}

		// Only the date but not the time
		if ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d)$/D', $exifString ) ) {
			return new \DateTime(
				substr( $exifString, 0, 4 ) . ':' .
				substr( $exifString, 5, 2 ) . ':' .
				substr( $exifString, 8, 2 ) . ' 00:00:00'
			);
		}

		return false;
	}

}
