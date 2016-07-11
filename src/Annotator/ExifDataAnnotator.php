<?php

namespace SESP\Annotator;

use SESP\PropertyRegistry;

use SMW\SemanticData;
use SMW\DIProperty;
use SMW\Subobject;

use SMWDataItem as DataItem;
use SMWDITime as DITime;
use SMWDIBlob as DIBlob;
use SMWDINumber as DINumber;

use FormatMetadata;
use Title;
use File;

use RuntimeException;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 * @author rotsee
 * @author Stephan Gambke
 */
class ExifDataAnnotator {

	/**
	 * @var SemanticData
	 */
	private $semanticData = null;

	/**
	 * @var File
	 */
	private $file = null;

	/**
	 * @var Subobject
	 */
	private $subobject = null;

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 * @param File $file
	 */
	public function __construct( SemanticData $semanticData, File $file ) {
		$this->semanticData = $semanticData;
		$this->file = $file;
	}

	/**
	 * @since 1.0
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
	}

	/**
	 * @since 1.0
	 *
	 * @return boolean
	 */
	public function addAnnotation() {

		if ( !$this->file->exists() ) {
			return false;
		}

		// #66
		$meta = $this->file->getMetadata();

		if ( !$meta ) {
			return false;
		}

		// Guard against "Error at offset 0 of 1 bytes"
		$exif = @unserialize( $meta );

		if ( !is_array( $exif ) || count( $exif ) === 0 ) {
			return false;
		}

		$exif[ 'ImageWidth' ]  = $this->file->getWidth();
		$exif[ 'ImageLength' ] = $this->file->getHeight();

		return $this->processExifData( $exif );
	}

	protected function processExifData( $rawExif ) {

		$this->subobject = new Subobject( $this->getSemanticData()->getSubject()->getTitle() );
		$this->subobject->setSemanticData( '_EXIFDATA' );

		$this->addPropertyValuesFromExifData( $rawExif );

		if ( $this->subobject->getSemanticData()->isEmpty() ) {
			return true;
		}

		$this->getSemanticData()->addPropertyObjectValue(
			new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '_EXIFDATA' ) ),
			$this->subobject->getContainer()
		);

		return true;
	}

	protected function addPropertyValuesFromExifData( $rawExif ) {

		$formattedExif = FormatMetadata::getFormattedData( $rawExif );

		foreach ( $formattedExif as $key => $value ) {

			$dataItem = null;
			$propertyId = PropertyRegistry::getInstance()->getPropertyId( $key );

			if ( $propertyId === null ) {
				continue;
			}

			$dataItemType = PropertyRegistry::getInstance()->getPropertyType( $key );

			switch ( $dataItemType ) {
				case DataItem::TYPE_NUMBER :
					$dataItem = is_numeric( $rawExif[$key] ) ? new DINumber( $rawExif[$key] ) : null;
					break;
				case DataItem::TYPE_BLOB :
					$dataItem = new DIBlob( $value );
					break;
				case DataItem::TYPE_TIME :
					$dataItem = $this->makeDataItemTime( $rawExif[$key] );
			}

			if ( $dataItem !== null ) {
				$this->subobject->getSemanticData()->addPropertyObjectValue(
					new DIProperty( $propertyId ),
					$dataItem
				);
			}

		}
	}

	protected function makeDataItemTime( $exifValue ) {
		$datetime = $this->convertExifDate( $exifValue );

		if ( $datetime ) {
			return new DITime(
				DITime::CM_GREGORIAN,
				$datetime->format('Y'),
				$datetime->format('n'),
				$datetime->format('j'),
				$datetime->format('G'),
				$datetime->format('i')
			);
		}
	}

	protected function convertExifDate( $exifString ) {

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
		if (  preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d)$/D', $exifString ) ) {
			return new \DateTime(
				substr( $exifString, 0, 4 ) . ':' .
				substr( $exifString, 5, 2 ) . ':' .
				substr( $exifString, 8, 2 ) . ' 00:00:00'
			);
		}

		return false;
	}

}
