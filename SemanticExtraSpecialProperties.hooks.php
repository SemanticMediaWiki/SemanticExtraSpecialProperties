<?php
/**
 * Helper class for implementing core functionality
 *
 * @author Leo Wallentin, mwjames, Stephan Gambke
 */

class SemanticESP {

	static private $mPropertyTypesToExifTags = array(
		'_num' => array(
			'OriginalImageHeight',
			'OriginalImageWidth',
			'PixelXDimension',
			'PixelYDimension',
			'ImageWidth',
			'ImageLength',
			'Rating',
		),
		'_dat' => array(
			'DateTime',
			'DateTimeOriginal',
			'DateTimeDigitized',
			'DateTimeReleased',
			'DateTimeExpires',
			'GPSDateStamp',
			'dc-date',
			'DateTimeMetadata',
		),
		'_txt' => array(
			'Compression',
			'PhotometricInterpretation',
			'Orientation',
			'PlanarConfiguration',
			'YCbCrPositioning',
			'XResolution',
			'YResolution',
			'ExifVersion',
			'FlashpixVersion',
			'ColorSpace',
			'ComponentsConfiguration',
			'ExposureProgram',
			'SubjectDistance',
			'MeteringMode',
			'LightSource',
			'Flash',
			'FocalPlaneResolutionUnit',
			'SensingMethod',
			'FileSource',
			'SceneType',
			'CustomRendered',
			'ExposureMode',
			'WhiteBalance',
			'SceneCaptureType',
			'GainControl',
			'Contrast',
			'Saturation',
			'Sharpness',
			'SubjectDistanceRange',
			'Make',
			'Model',
			'Software',
			'ExposureTime',
			'ISOSpeedRatings',
			'FNumber',
			'FocalLength',
			'FocalLengthIn35mmFilm',
			'MaxApertureValue',
			'iimCategory',
			'SubjectNewsCode',
			'Urgency',
			'ImageDescription',
			'Artist',
			'Copyright',
			'RelatedSoundFile',
			'ImageUniqueID',
			'SpectralSensitivity',
			'GPSSatellites',
			'GPSVersionID',
			'GPSMapDatum',
			'Keywords',
			'WorldRegionDest',
			'CountryDest',
			'CountryCodeDest',
			'ProvinceOrStateDest',
			'CityDest',
			'SublocationDest',
			'WorldRegionCreated',
			'CountryCreated',
			'CountryCodeCreated',
			'ProvinceOrStateCreated',
			'CityCreated',
			'SublocationCreated',
			'ObjectName',
			'SpecialInstructions',
			'Headline',
			'Credit',
			'Source',
			'EditStatus',
			'FixtureIdentifier',
			'LocationDest',
			'LocationDestCode',
			'Writer',
			'JPEGFileComment',
			'iimSupplementalCategory',
			'OriginalTransmissionRef',
			'Identifier',
			'dc-contributor',
			'dc-coverage',
			'dc-publisher',
			'dc-relation',
			'dc-rights',
			'dc-source',
			'dc-type',
			'Lens',
			'SerialNumber',
			'CameraOwnerName',
			'Label',
			'Nickname',
			'RightsCertificate',
			'CopyrightOwner',
			'UsageTerms',
			'WebStatement',
			'OriginalDocumentID',
			'LicenseUrl',
			'MorePermissionsUrl',
			'AttributionUrl',
			'PreferredAttributionName',
			'PNGFileComment',
			'Disclaimer',
			'ContentWarning',
			'GIFFileComment',
			'SceneCode',
			'IntellectualGenre',
			'Event',
			'OrginisationInImage',
			'PersonInImage',
			'ObjectCycle',
			'Copyrighted',
			'LanguageCode',
		),
	);

	/**
	 * @brief  Initializes all properties, hooks into smwInitProperties.
	 *
	 * @return true
	 */
	public static function sespInitProperties() {

		// Page author
		self::registerProperty( '___EUSER', '_wpg', 'sesp-property-author', 'Page author' );

		// Page creator
		self::registerProperty( '___CUSER', '_wpg', 'sesp-property-first-author', 'Page creator' );

		// Revision ID
		self::registerProperty( '___REVID', '_num', 'sesp-property-revision-id', 'Revision ID' );

		// View count
		self::registerProperty( '___VIEWS', '_num', 'sesp-property-view-count', 'Number of page views' );

		// Sub pages
		self::registerProperty( '___SUBP', '_wpg', 'sesp-property-subpages', 'Subpage' );

		// Number of revisions
		self::registerProperty( '___NREV', '_num', 'sesp-property-revisions', 'Number of revisions' );

		// Number of talk page revisions
		self::registerProperty( '___NTREV', '_num', 'sesp-property-talk-revisions', 'Number of talk page revisions' );

		// MIME type
		self::registerProperty( '___MIMETYPE', '_txt', 'sesp-property-mimetype', 'MIME type' );

		// MIME type
		self::registerProperty( '___MEDIATYPE', '_txt', 'sesp-property-mediatype', 'Media type' );

		// SHORTURL type
		self::registerProperty( '___SHORTURL', '_uri', 'sesp-property-shorturl', 'Short URL' );

		// User registration date
		self::registerProperty( '___USERREG', '_dat', 'sesp-property-user-registration-date', 'User registration date' );

		// Image METADATA types
		self::registerProperty( '___EXIFDATA', '_wpg', 'sesp-property-exif-data' );

		foreach ( self::$mPropertyTypesToExifTags as $type => $exifTags) {
			foreach ( $exifTags as $exifTag ) {
				self::registerExifProperty( $exifTag, $type );
			}
		}

		// Compatibility wit previous SESP versions
		SMWDIProperty::registerPropertyAlias( '___EXIFDATETIMEORIGINAL', 'Exposure date' );
		SMWDIProperty::registerPropertyAlias( '___EXIFSOFTWARE', 'Software' );

		return true;
	} // end sespInitProperties()

	/**
	 * @brief Adds the properties, hooks into SMWStore::updateDataBefore.
	 *
	 * @param SMWStore $store
	 * @param SMWSemanticData $data
	 *
	 * @return     true
	 *
	 */
	public static function sespUpdateDataBefore( SMWStore $store, SMWSemanticData $data ) {
		global $sespSpecialProperties, $wgDisableCounters;

		// just some compat mode
		global $smwgPageSpecialProperties2;

		if ( isset( $smwgPageSpecialProperties2 ) && !isset( $sespSpecialProperties ) ) {
			$sespSpecialProperties = $smwgPageSpecialProperties2;
		}

		/* Get array of properties to set */
		if ( !isset( $sespSpecialProperties ) ) {
			wfDebug( __METHOD__ . ": SESP array is not specified, please add the following\n" );
			wfDebug( "variables to your LocalSettings.php:\n" );
			wfDebug( "\$sespSpecialProperties\n" );
			return true;
		}

		/* Get current title and wikipage  */
		$subject = $data->getSubject();
		$title = Title::makeTitle( $subject->getNamespace(), $subject->getDBkey() );
		$wikipage = WikiPage::factory( $title );

		// return if $title or $wikipage is null
		if ( is_null( $title ) || is_null( $wikipage ) ) {
			return true;
		}

		/**************************/
		/* CUSER (First author)   */
		/**************************/
		if ( in_array( '_CUSER', $sespSpecialProperties ) ) {

			// TODO: remove else branch when compatibility to MW pre1.20 is dropped
			if ( method_exists( $wikipage, 'getCreator' ) ) {

				$firstAuthor = $wikipage->getCreator();
			} else {

				$firstRevision = $title->getFirstRevision();

				if ( $firstRevision !== null ) {
					$firstAuthor = User::newFromId( $firstRevision->getRawUser() );
				}
			}

			if ( $firstAuthor ) {
				$property = new SMWDIProperty( '___CUSER' );
				$dataItem = SMWDIWikiPage::newFromTitle( $firstAuthor->getUserPage() );
				$data->addPropertyObjectValue( $property, $dataItem );
			}
		} // end if _CUSER

		/**************************/
		/* REVID (Revision ID)    */
		/**************************/
		if ( in_array( '_REVID', $sespSpecialProperties ) ) {
			$property = new SMWDIProperty( '___REVID' );
			$dataItem = new SMWDINumber( $wikipage->getId() );
			$data->addPropertyObjectValue( $property, $dataItem );
		}

		/********************************/
		/* VIEWS (Number of page views) */
		/********************************/
		if ( in_array( '_VIEWS', $sespSpecialProperties ) && !$wgDisableCounters ) {
			$property = new SMWDIProperty( '___VIEWS' );
			$dataItem = new SMWDINumber( $wikipage->getCount() );
			$data->addPropertyObjectValue( $property, $dataItem );
		}

		/*****************************/
		/* EUSER (Page contributors) */
		/*****************************/
		if ( in_array( '_EUSER', $sespSpecialProperties ) ) {
			/* Create property */
			$property = new SMWDIProperty( '___EUSER' );
			/* Get options */
			global $wgSESPExcludeBots;
			if ( !isset( $wgSESPExcludeBots ) )
				$wgSESPExcludeBots = false;

			/* Get author from current revision */
			$u = User::newFromId( $wikipage->getUser() );
			/* Get authors from earlier revisions */
			$authors = $wikipage->getContributors();

			while ( $u ) {
				if ( !( in_array( 'bot', $u->getRights() ) && $wgSESPExcludeBots ) // exclude bots?
						&& !$u->isAnon() ) { // no anonymous users (hidden users are not returned)
					/* Add values */
					$dataItem = SMWDIWikiPage::newFromTitle( $u->getUserPage() );
					$data->addPropertyObjectValue( $property, $dataItem );
				}// if
				$u = $authors->current();
				$authors->next();
			}// while u
		}

		/******************************/
		/* NREV (Number of revisions) */
		/******************************/
		if ( in_array( '_NREV', $sespSpecialProperties ) ) {
			/* Create property */
			$property = new SMWDIProperty( '___NREV' );
			/* Get number of revisions */
			$dbr =& wfGetDB( DB_SLAVE );
			$num = $dbr->estimateRowCount( "revision", "*", array( "rev_page" => $title->getArticleID() ) );

			/* Add values */
			$dataItem = new SMWDINumber( $num );
			$data->addPropertyObjectValue( $property, $dataItem );
		}

		/*****************************************/
		/* NTREV (Number of talk page revisions) */
		/*****************************************/
		if ( in_array( '_NTREV', $sespSpecialProperties ) ) {
			/* Create property */
			$property = new SMWDIProperty( '___NTREV' );
			/* Get number of revisions */
			if ( !isset( $dbr ) ){
				$dbr = & wfGetDB( DB_SLAVE );
			}

			$talkPage = $title->getTalkPage();
			$num = $dbr->estimateRowCount( "revision", "*", array( "rev_page" => $talkPage->getArticleID() ) );

			/* Add values */
			$dataItem = new SMWDINumber( $num );
			$data->addPropertyObjectValue( $property, $dataItem );
		}

		/************************/
		/* SUBP (Get sub pages) */
		/************************/
		if ( in_array( '_SUBP', $sespSpecialProperties ) ) {
			/* Create property */
			$property = new SMWDIProperty( '___SUBP' );
			$subpages = $title->getSubpages( -1 ); // -1 = no limit. Returns TitleArray object

			/* Add values */
			foreach ( $subpages as $t ) {
				$dataItem = SMWDIWikiPage::newFromTitle( $t );
				$data->addPropertyObjectValue( $property, $dataItem );
			}  // end foreach
		} // end _SUBP

		/************************/
		/* MIMETYPE             */
		/************************/
		if ( $title->inNamespace( NS_FILE ) && in_array( '_MIMETYPE', $sespSpecialProperties ) ) {

			// Build image page instance
			$imagePage = new ImagePage( $title );
			$file = $imagePage->getFile();
			$mimetype = $file->getMimeType();
			$mediaType = MimeMagic::singleton()->findMediaType( $mimetype );
			list( $mimetypemajor, $mimetypeminor ) = $file->splitMime( $mimetype );

			// MIMETYPE
			$property = new SMWDIProperty( '___MIMETYPE' );
			$dataItem = new SMWDIString( $mimetypeminor );
			$data->addPropertyObjectValue( $property, $dataItem );

			// MEDIATYPE
			$property = new SMWDIProperty( '___MEDIATYPE' );
			$dataItem = new SMWDIString( $mediaType );
			$data->addPropertyObjectValue( $property, $dataItem );
		} // end if MIMETYPE

		/************************/
		/* IMAGEMETA            */
		/************************/
		if ( in_array( '_METADATA', $sespSpecialProperties ) ) {
			self::updateImageMetaData( $store, $data );
		}

		/************************/
		/* SHORTURL             */
		/************************/
		// FIXME: handle internal and external links

		if ( in_array( '_SHORTURL', $sespSpecialProperties ) && class_exists( 'ShortUrlUtils' ) ) {
			global $wgShortUrlPrefix;

			if ( !is_string( $wgShortUrlPrefix ) ) {
				$urlPrefix = SpecialPage::getTitleFor( 'ShortUrl' )->getFullUrl() . '/';
			} else {
				$urlPrefix = $wgShortUrlPrefix;
			}

			if ( ShortUrlUtils::needsShortUrl( $title ) ) {
				$shortId = ShortUrlUtils::encodeTitle( $title );
				$shortURL = $urlPrefix . $shortId;

				$property = new SMWDIProperty( '___SHORTURL' );
				$dataItem = new SMWDIUri( 'http', $shortURL, '', '' );

				$data->addPropertyObjectValue( $property, $dataItem );
			}

		} // end if SHORTURL

		/************************/
		/* USERREG              */
		/************************/

		if ( in_array( '_USERREG', $sespSpecialProperties ) && $title->inNamespace( NS_USER ) ) {

			$property = new SMWDIProperty( '___USERREG' );

			$u = User::newFromName( $title->getText() );
			if ( $u ) {

				$d = $u->getRegistration(); // Mediawiki timestamp (20110125223011)
				$d = wfTimestamp( TS_ISO_8601, $d );
				$d = new DateTime( $d );

				$dataItem = new SMWDITime( SMWDITime::CM_GREGORIAN, $d->format( 'Y' ), $d->format( 'm' ), $d->format( 'd' ), $d->format( 'H' ), $d->format( 'i' ) );
				$data->addPropertyObjectValue( $property, $dataItem );
			}

		} // end USERREG

		return true;
	} // end sespUpdateDataBefore()

	/**
	 * @brief Adds the exif properties
	 *
	 * @param SMWStore $store
	 * @param SMWSemanticData $data
	 *
	 */
	public static function updateImageMetaData( SMWStore $store, SMWSemanticData $data ) {

		// Get current wikipage and title
		$subject = $data->getSubject();
		$title = $subject->getTitle();

		// return if not in File namespece
		if ( !$title->inNamespace( NS_FILE ) ) {
			return;
		}

		// get exif data
		$imagePage = new ImagePage( $title );
		$file = $imagePage->getFile();
		$exif = unserialize( $file->getMetadata() );

		if ( !is_array( $exif ) || count( $exif ) === 0 ) {
			return;
		}

		$exif[ 'ImageWidth' ] = $file->getWidth();
		$exif[ 'ImageLength' ] = $file->getHeight();

		$formattedExif = FormatMetadata::getFormattedData( $exif );

		// log exif data to log if log group exif is specified
		wfDebugLog( 'exif', "\n" . $title->getFullText() . "\nFORMATTED EXIF DATA: " . var_export($formattedExif, true), false );

		// create semantic data container for Exif data subobject
		$diSubobject = new SMWDIWikiPage(
						$subject->getDBkey(),
						$subject->getNamespace(),
						$subject->getInterwiki(),
						'EXIF_data' );

		$subData = new SMWContainerSemanticData( $diSubobject );

		// populate the container

		foreach ( $formattedExif as $key => $value ) {

			$propId = self::exifIdToPropId( $key ) ;
			$propTypeId = SMWDIProperty::getPredefinedPropertyTypeId( $propId );

			switch ( $propTypeId ) {
				case '_dat':

					$val = $exif[$key];

					if ( $val == '0000:00:00 00:00:00' || $val == '    :  :     :  :  ' ) {
						// ignore unknown dates
						continue 2;
					} elseif ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d) (?:\d\d):(?:\d\d):(?:\d\d)$/D', $val ) ) {
						// Full date. Us as is.
					} elseif ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d) (?:\d\d):(?:\d\d)$/D', $val ) ) {
						// No second field. Still format the same
						// since timeanddate doesn't include seconds anyways,
						// but second still available in api
						$val .= ':00';
					} elseif ( preg_match( '/^(?:\d{4}):(?:\d\d):(?:\d\d)$/D', $val ) ) {
						// If only the date but not the time is filled in.
						$val = substr( $val, 0, 4 ) . ':' .
								substr( $val, 5, 2 ) . ':' .
								substr( $val, 8, 2 ) . ' 00:00:00';
					} else {
						// continue and try to parse anyway
					}

					$datetime = new DateTime( $val );

					if ( $datetime ) {
						$dataItem = new SMWDITime(
										SMWDITime::CM_GREGORIAN,
										$datetime->format( 'Y' ),
										$datetime->format( 'n' ),
										$datetime->format( 'j' ),
										$datetime->format( 'G' ),
										$datetime->format( 'i' ),
										$datetime->format( 's' )
						);
					} else {
						continue 2;
					}

					break;
				case '_txt':
					$dataItem = new SMWDIBlob( $value );
					break;
				case '_num':
					$dataItem = new SMWDINumber( $exif[$key] );
					break;
				default:
					continue 2; // need to exit 2 levels, PHP considers switch a loop o_O
			}

			$property = new SMWDIProperty( $propId );
			$subData->addPropertyObjectValue( $property, $dataItem );
		}

//			// EXIFLATLON
//			/*
//			  //TODO
//			  if ( array_key_exists( 'GPSLatitudeRef', $exif ) || array_key_exists( 'GPSLongitudeRef', $exif ) ) {
//			  } */// EXIFLATLON

		$data->addSubSemanticData( $subData );

		// Store subobject to the semantic data instance
		$data->addPropertyObjectValue(
				new SMWDIProperty( SMWDIProperty::TYPE_SUBOBJECT ), new SMWDIContainer( $subData )
		);

		$data->addPropertyObjectValue(
				new SMWDIProperty( '___EXIFDATA' ), //SMWDIProperty( SMWDIProperty::TYPE_WIKIPAGE ),
				$diSubobject
		);
	}

	/**
	 * Registers a predefined property for SMW.
	 *
	 * This method registers a property with the given id using the message
	 * in the wiki content language identified by the msgKey as property name.
	 * The message in English is registered as an alias property name.
	 *
	 * For backward compatibility an alternative alias may be registered.
	 * TODO: Remove alternative alias in some future version
	 *
	 * @since 0.2.8
	 *
	 * @param string $id property id
	 * @param string $typeid SMW type id
	 * @param string $msgKey lookup key for the message used as property name
	 * @param String $altAlias alternative alias
	 */
	protected static function registerProperty ( $id, $typeid, $msgKey, $altAlias = null ) {

		$message = wfMessage( $msgKey );
		SMWDIProperty::registerProperty( $id, $typeid, $message->text(), true );

		$message->inLanguage( 'en' );
		SMWDIProperty::registerPropertyAlias( $id, $message->text() );

		if ( $altAlias !== null ) {
			SMWDIProperty::registerPropertyAlias( $id, $altAlias );
		}
	}

	protected static function exifIdToPropId ( $exifId ) {
		return '___EXIF' . strtoupper($exifId);
	}

	protected static function registerExifProperty ( $exifId, $typeid, $altAlias = null ) {
		self::registerProperty( self::exifIdToPropId( $exifId ), $typeid, 'exif-' . strtolower($exifId), $altAlias);
	}

} // end of class SemanticESP
