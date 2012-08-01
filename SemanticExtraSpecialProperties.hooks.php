<?php
/**
 * Helper class for implementing core functionality 
 *
 * @author Leo Wallentin, mwjames
 */

class SemanticESP {

 /**
  * @brief  Initializes all properties, hooks into smwInitProperties.
  *
  * @return true
  */
  public static function sespInitProperties() {

   // Page author
   SMWDIProperty::registerProperty( '___EUSER', '_wpg',
   wfMsgForContent('sesp-property-author') );
   SMWDIProperty::registerPropertyAlias( '___EUSER', 'Page author' );

   // Page creator
   SMWDIProperty::registerProperty( '___CUSER', '_wpg',
   wfMsgForContent('sesp-property-first-author') );
   SMWDIProperty::registerPropertyAlias( '___CUSER', 'Page creator' );

   // Revision ID
   SMWDIProperty::registerProperty( '___REVID', '_num',
   wfMsgForContent('sesp-property-revision-id') );
   SMWDIProperty::registerPropertyAlias( '___REVID', 'Revision ID' );

   //View count
   SMWDIProperty::registerProperty( '___VIEWS', '_num',
   wfMsgForContent('sesp-property-view-count') );
   SMWDIProperty::registerPropertyAlias( '___VIEWS', 'Number of page views' );

   //Sub pages
   SMWDIProperty::registerProperty( '___SUBP', '_wpg',
   wfMsgForContent('sesp-property-subpages') );
   SMWDIProperty::registerPropertyAlias( '___SUBP', 'Subpage' );

   //Number of revisions
   SMWDIProperty::registerProperty( '___NREV', '_num',
   wfMsgForContent('sesp-property-revisions') );
   SMWDIProperty::registerPropertyAlias( '___NREV', 'Number of revisions' );

   //Number of talk page revisions
   SMWDIProperty::registerProperty( '___NTREV', '_num',
   wfMsgForContent('sesp-property-talk-revisions') );
   SMWDIProperty::registerPropertyAlias( '___NTREV', 'Number of talk page revisions' );

   // MIME type
   SMWDIProperty::registerProperty( '___MIMETYPE', '_str',
   wfMsgForContent('sesp-property-mimetype') );
   SMWDIProperty::registerPropertyAlias( '___MIMETYPE', 'MIME type' );

   // MIME type
   SMWDIProperty::registerProperty( '___MEDIATYPE', '_str',
   wfMsgForContent('sesp-property-mediatype') );
   SMWDIProperty::registerPropertyAlias( '___MEDIATYPE', 'Media type' );

   // SHORTURL type
   SMWDIProperty::registerProperty( '___SHORTURL', '_uri',
   wfMsgForContent('sesp-property-shorturl') );
   SMWDIProperty::registerPropertyAlias( '___SHORTURL', 'Short URL' );

   // METADATA types
   SMWDIProperty::registerProperty( '___EXIFDATETIME', '_dat',
   wfMsgForContent('exif-datetimeoriginal') );
   SMWDIProperty::registerPropertyAlias( '___EXIFDATETIME', 'Exposure date' );

   SMWDIProperty::registerProperty( '___EXIFSOFTWARE', '_str',
   wfMsgForContent('exif-software') );
   SMWDIProperty::registerPropertyAlias( '___EXIFSOFTWARE', 'Software' );


   return true;
 } // end sespInitProperties()

 /**
  * @brief      Adds the properties, hooks into SMWStore::updateDataBefore.
  *
  * @param      SMWStore $store, SMWSemanticData $newData
  *
  * @return     true
  *
  */
  public function sespUpdateDataBefore ( $store, $data ) {
   global $sespSpecialProperties, $wgDisableCounters;

   // just some compat mode
   global $smwgPageSpecialProperties2;
   if ( isset( $smwgPageSpecialProperties2) && !isset( $sespSpecialProperties ) )
	$sespSpecialProperties = $smwgPageSpecialProperties2;

  /* Get array of properties to set */
  if ( !isset( $sespSpecialProperties ) ) {
   wfDebug( __METHOD__ . ": SESP array is not specified, please add the following\n" );
   wfDebug( "variables to your LocalSettings.php:\n" );
   wfDebug( "\$sespSpecialProperties\n" );
   return true;
  }

  /* Get current title and article */
  $title   = $data->getSubject()->getTitle();
  $article = Article::newFromTitle( $title, RequestContext::getMain() );

  // return if $title or $article is null
  if ( is_null( $title ) || is_null( $article) ) {
   return true;
  }

  /**************************/
  /* CUSER (First author)   */
  /**************************/
  if ( in_array( '_CUSER', $sespSpecialProperties ) ) {

   $firstRevision = $title->getFirstRevision();

   if ( $firstRevision !== null ) {

    $firstAuthor = User::newFromId( $firstRevision->getRawUser () );

    if ($firstAuthor) {
     $property = new SMWDIProperty( '___CUSER' );
     $dataItem = SMWDIWikiPage::newFromTitle( $firstAuthor->getUserPage() );
     $data->addPropertyObjectValue( $property, $dataItem );
    }

   }
  } // end if _CUSER

  /**************************/
  /* REVID (Revision ID)    */
  /**************************/
  if ( in_array( '_REVID', $sespSpecialProperties ) ) {
   $property = new SMWDIProperty( '___REVID' );
   $dataItem = new SMWDINumber( $article->getRevIdFetched() );
   $data->addPropertyObjectValue( $property, $dataItem );
  }

  /********************************/
  /* VIEWS (Number of page views) */
  /********************************/
  if ( in_array( '_VIEWS', $sespSpecialProperties ) && !$wgDisableCounters ) {
   $property = new SMWDIProperty( '___VIEWS' );
   $dataItem = new SMWDINumber( $article->getCount() );
   $data->addPropertyObjectValue ( $property, $dataItem );
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
  $u = User::newFromId( $article->getUser() );
  /* Get authors from earlier revisions */
  $authors = $article->getContributors();

  while ( $u ) {
   if (    !$u->isHidden()  //don't list hidden users
     && !(in_array( 'bot', $u->getRights() ) && $wgSESPExcludeBots) //exclude bots?
     && !$u->isAnon () ) { //no anonymous users
     /* Add values*/
     $dataItem = SMWDIWikiPage::newFromTitle( $u->getUserPage() );
     $data->addPropertyObjectValue( $property, $dataItem );
	 }
	  $u = $authors->current();
		$authors->next();
	 }
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
   $data->addPropertyObjectValue ( $property, $dataItem );
  }

  /*****************************************/
  /* NTREV (Number of talk page revisions) */
  /*****************************************/
  if ( in_array( '_NTREV', $sespSpecialProperties ) ) {
   /* Create property */
   $property = new SMWDIProperty( '___NTREV' );
   /* Get number of revisions */
   if ( !isset( $dbr ) )
    $dbr =& wfGetDB( DB_SLAVE );
    $talkPage = $title->getTalkPage ();
    $num = $dbr->estimateRowCount( "revision", "*", array( "rev_page" => $talkPage->getArticleID() ) );;
    
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
   $subpages = $title->getSubpages ( -1 ); //-1 = no limit. Returns TitleArray object

   /* Add values*/
   foreach ( $subpages as $t ) {
    $dataItem = SMWDIWikiPage::newFromTitle( $t );
    $data->addPropertyObjectValue( $property, $dataItem );
   }  // end foreach
  } // end _SUBP

  /************************/
  /* MIMETYPE */
  /************************/
  if ( $title->inNamespace( NS_FILE ) && in_array( '_MIMETYPE', $sespSpecialProperties ) ) {

   // Build image page instance
   $imagePage  = new ImagePage( $title );
   $file       = $imagePage->getFile();		
   $mimetype   = $file->getMimeType();
   $mediaType  = MimeMagic::singleton()->findMediaType($mimetype);
   list( $mimetypemajor, $mimetypeminor ) = $file->splitMime($mimetype);
   
   // MIMETYPE 
   $property = new SMWDIProperty( '___MIMETYPE' );
   $dataItem = new SMWDIString( $mimetypeminor );
   $data->addPropertyObjectValue ($property, $dataItem);

   // MEDIATYPE 
   $property = new SMWDIProperty( '___MEDIATYPE' );
   $dataItem = new SMWDIString( $mediaType );
   $data->addPropertyObjectValue ($property, $dataItem);
   
  } // end if MIMETYPE

  /************************/
  /* IMAGEMETA */
  /************************/

  if ( $title->inNamespace( NS_FILE ) && in_array( '_METADATA', $sespSpecialProperties ) ) {
   $imagePage  = new ImagePage( $title );
   $file       = $imagePage->getFile();
   $metadata = $file->getMetadata();


  if ( $metadata === ExifBitmapHandler::OLD_BROKEN_FILE ||
        $metadata === ExifBitmapHandler::BROKEN_FILE /*||
	ExifBitmapHandler::isMetadataValid( $file, $metadata ) === ExifBitmapHandler::METADATA_BAD //Too picky... */ ) {
    // So we don't try and display metadata from PagedTiffHandler
    // for example when using InstantCommons.
    return true;
   }

   $exif = unserialize( $metadata );
   if ( $exif ) {
    if ( count( $exif ) ) {

     // EXIFDATETIME 
     if ( array_key_exists( 'DateTimeOriginal', $exif ) || array_key_exists( 'DateTime', $exif ) ) {
      $property = new SMWDIProperty( '___EXIFDATETIME' );
      if ( array_key_exists( 'DateTimeOriginal', $exif ) ) {
       $exifstr = $exif['DateTimeOriginal'];
      } else {
       $exifstr = $exif['DateTime'];
      }
      $datetime = $this->convertexifdate( $exifstr );

      if ( $datetime ) {
       $dataItem = new SMWDITime( SMWDITime::CM_GREGORIAN, $datetime->format('Y'), $datetime->format('n'), $datetime->format('j'), $datetime->format('G'), $datetime->format('i') );
       $data->addPropertyObjectValue ($property, $dataItem);
      }
     }

     // EXIFSOFTWARE
     if ( array_key_exists( 'Software', $exif ) || ( array_key_exists( 'metadata', $exif ) && array_key_exists( 'Software', $exif['metadata'] )) ) {

      $str = array_key_exists( 'Software', $exif ) ? $exif['Software'] : $exif['metadata']['Software'];
      if ( is_array( $str) ) {
       $str = array_key_exists( 'x-default', $str ) ? $str['x-default'] : $str[0];
      }
      if ( !$str )
       return true;

      $property = new SMWDIProperty( '___EXIFSOFTWARE' );
      $dataItem = new SMWDIString( $str );
      $data->addPropertyObjectValue ($property, $dataItem);
     }

     // EXIFLATLON
/*
//TODO
     if ( array_key_exists( 'GPSLatitudeRef', $exif ) || array_key_exists( 'GPSLongitudeRef', $exif ) ) {
     } *///EXIFLATLON

    }
   }
  }//IMAGEMETA

  /************************/
  /* SHORTURL             */
  /************************/
//FIXME handle internal and external links

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

    $data->addPropertyObjectValue ($property, $dataItem);
   } else {
   }
   
  } // end if SHORTURL
        
 return true;
 } // end sespUpdateDataBefore()


 /* Make Exif dates something SMW can store */
 function convertexifdate ( $exifString ) {
  $exifPieces = explode(":", $exifString);
  if ( $exifPieces[0] && $exifPieces[1] && $exifPieces[2] ) {
   $res = new DateTime($exifPieces[0] . "-" . $exifPieces[1] .
   "-" . $exifPieces[2] . ":" . $exifPieces[3] . ":" . $exifPieces[4]);
   return $res;
  } else {
   return false;
  } 
 }

} // end of class SemanticESP
