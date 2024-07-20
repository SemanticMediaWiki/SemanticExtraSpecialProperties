<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use File;
use Title;

class PageImagesPropertyAnnotator implements PropertyAnnotator {

    /**
     * Predefined property ID
     */
    const PROP_ID = '___PAGEIMG';

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
        $Title =  $semanticData->getSubject()->getTitle();
        $pageImageTitle = $this->getPageImageTitle( $Title );

        if ( $pageImageTitle ){
            $semanticData->addPropertyObjectValue( $property, DIWikiPage::newFromTitle( $pageImageTitle ) );
        }
    }

    /**
     * PageImage (Title)
     * @param $Title Title
     * @return Title|bool
     */
    protected function getPageImageTitle( Title $Title ) {
        $imageFile = $this->getPageImage( $Title );

        if( $imageFile ){
            if ( $imageFile->getTitle() instanceof Title ) {
                return $imageFile->getTitle();
            }
        }
        return false;
    }

    /**
     * PageImage (File)
     * @param $Title Title
     * @return File|bool
     */
    protected function getPageImage( Title $Title ) {
        if ( class_exists( '\PageImages\PageImages' ) ) {
            return \PageImages\PageImages::getPageImage( $Title );
        }
        return false;
    }
}