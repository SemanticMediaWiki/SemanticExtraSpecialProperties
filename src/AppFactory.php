<?php

namespace SESP;

use SESP\Annotator\ShortUrlAnnotator;
use SESP\Annotator\ExifDataAnnotator;
use SMW\SemanticData;
use File;
use Title;

/**
 * @license GNU GPL v2+
 * @since 1.3
 *
 * @author mwjames
 */
class AppFactory {

	/**
	 * @var string
	 */
	private $shortUrlPrefix;

	public function __construct( $shortUrlPrefix = '' ) {
		$this->shortUrlPrefix = $shortUrlPrefix;
	}

	/**
	 * @since 1.3
	 *
	 * @return DatabaseBase
	 */
	public function newDatabaseConnection() {
		return wfGetDB( DB_SLAVE );
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 *
	 * @return WikiPage
	 */
	public function newWikiPage( Title $title ) {
		return \WikiPage::factory( $title );
	}

	/**
	 * @since 1.3
	 *
	 * @param Title $title
	 *
	 * @return User
	 */
	public function newUserFromTitle( Title $title ) {
		return \User::newFromName( $title->getText() );
	}

	/**
	 * @since 1.3
	 *
	 * @param SemanticData $semanticData
	 *
	 * @return ShortUrlAnnotator
	 */
	public function newShortUrlAnnotator( SemanticData $semanticData ) {

		$shortUrlAnnotator = new ShortUrlAnnotator( $semanticData );
		$shortUrlAnnotator->setShortUrlPrefix( $this->shortUrlPrefix );

		return $shortUrlAnnotator;
	}

	/**
	 * @since 1.3
	 *
	 * @param SemanticData $semanticData
	 * @param File $file
	 *
	 * @return ExifDataAnnotator
	 */
	public function newExifDataAnnotator( SemanticData $semanticData, File $file ) {
		return new ExifDataAnnotator( $semanticData, $file );
	}

}
