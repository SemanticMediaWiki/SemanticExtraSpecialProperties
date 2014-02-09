<?php

namespace SESP;

use SMW\SemanticData;
use SMW\DIProperty;

use SMWDITime as DITime;
use SMWDIBlob as DIBlob;

use ExifBitmapHandler;
use ImagePage;
use Title;

/**
 * @ingroup SESP
 *
 * @licence GNU GPL v2+
 * @since 0.3
 *
 */
class ImageMetadataAnnotator {

	/** @var SemanticData */
	protected $semanticData = null;
	protected $metadata = null;

	/**
	 * @since 0.3
	 *
	 * @param SemanticData $semanticData
	 */
	public function __construct( SemanticData $semanticData ) {
		$this->semanticData = $semanticData;
	}

	/**
	 * @since 0.3
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {
		return $this->semanticData;
	}

	/**
	 * @since 0.3
	 *
	 * @return boolean
	 */
	public function addAnnotation() {

		$metadata = $this->getMetadata();

		if ( $metadata === ExifBitmapHandler::OLD_BROKEN_FILE ||
					$metadata === ExifBitmapHandler::BROKEN_FILE /*||
		ExifBitmapHandler::isMetadataValid( $file, $metadata ) === ExifBitmapHandler::METADATA_BAD //Too picky... */ ) {
			// So we don't try and display metadata from PagedTiffHandler
			// for example when using InstantCommons.
			return true;
		}

		$exif = unserialize( $metadata );

		if ( $exif && count( $exif ) ) {
			$this->addPropertyValueForExifDate( $exif );
			$this->addPropertyValueForExifSoftware( $exif );
		}

		// EXIFLATLON
		/*
		//TODO
		if ( array_key_exists( 'GPSLatitudeRef', $exif ) || array_key_exists( 'GPSLongitudeRef', $exif ) ) {
		} *///EXIFLATLON

		return true;
	}

	protected function getMetadata() {

		if ( $this->metadata === null ) {
			$imagePage = new ImagePage( $this->getSemanticData()->getSubject()->getTitle() );
			$this->metadata = $imagePage->getFile()->getMetadata();
		}

		return $this->metadata;
	}

	protected function addPropertyValueForExifDate( $exif ) {

		if ( array_key_exists( 'DateTimeOriginal', $exif ) || array_key_exists( 'DateTime', $exif ) ) {

			if ( array_key_exists( 'DateTimeOriginal', $exif ) ) {
				$exifstr = $exif['DateTimeOriginal'];
			} else {
				$exifstr = $exif['DateTime'];
			}

			$datetime = $this->convertExifDate( $exifstr );

			if ( $datetime ) {
				$dataItem = new DITime(
					DITime::CM_GREGORIAN,
					$datetime->format('Y'),
					$datetime->format('n'),
					$datetime->format('j'),
					$datetime->format('G'),
					$datetime->format('i')
				);

				$this->getSemanticData()->addPropertyObjectValue(
					new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '___EXIFDATETIME' ) ),
					$dataItem
				);
			}
		}
	}

	protected function addPropertyValueForExifSoftware( $exif ) {
		if ( array_key_exists( 'Software', $exif ) || ( array_key_exists( 'metadata', $exif ) && array_key_exists( 'Software', $exif['metadata'] )) ) {

			$str = array_key_exists( 'Software', $exif ) ? $exif['Software'] : $exif['metadata']['Software'];

			if ( is_array( $str ) ) {
				$str = array_key_exists( 'x-default', $str ) ? $str['x-default'] : $str[0];
			}

			if ( $str ) {
				$this->getSemanticData()->addPropertyObjectValue(
					new DIProperty( PropertyRegistry::getInstance()->getPropertyId( '___EXIFSOFTWARE' ) ),
					new DIBlob( $str )
				);
			}
		}
	}

	protected function convertExifDate( $exifString ) {
		$exifPieces = explode(":", $exifString);
		if ( $exifPieces[0] && $exifPieces[1] && $exifPieces[2] ) {
			$res = new \DateTime($exifPieces[0] . "-" . $exifPieces[1] .
			"-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4]);
			return $res;
		} else {
			return false;
		}
	}

}
