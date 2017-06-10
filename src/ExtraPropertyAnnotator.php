<?php

namespace SESP;

use SMW\DIProperty;
use SMW\SemanticData;
use SESP\PropertyAnnotators\NullPropertyAnnotator;
use SESP\PropertyAnnotators\CreatorPropertyAnnotator;
use SESP\PropertyAnnotators\PageViewsPropertyAnnotator;
use SESP\PropertyAnnotators\LocalPropertyAnnotator;
use SESP\PropertyAnnotators\UserRegistrationDatePropertyAnnotator;
use SESP\PropertyAnnotators\UserEditCountPropertyAnnotator;
use SESP\PropertyAnnotators\PageIDPropertyAnnotator;
use SESP\PropertyAnnotators\ShortUrlPropertyAnnotator;
use SESP\PropertyAnnotators\ExifPropertyAnnotator;
use SESP\PropertyAnnotators\RevisionIDPropertyAnnotator;
use SESP\PropertyAnnotators\PageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\TalkPageNumRevisionPropertyAnnotator;
use SESP\PropertyAnnotators\PageContributorsPropertyAnnotator;
use SESP\PropertyAnnotators\SubPagePropertyAnnotator;
use SESP\PropertyAnnotators\PageLengthPropertyAnnotator;

/**
 * @private
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class ExtraPropertyAnnotator {

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var PropertyAnnotator[]
	 */
	private $propertyAnnotators = array();

	/**
	 * @var PropertyAnnotator
	 */
	private $localPropertyAnnotator;

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
	 * @param SemanticData $semanticData
	 */
	public function addAnnotation( SemanticData $semanticData ) {

		$time = microtime( true );

		if ( !$this->canAnnotate( $semanticData->getSubject() ) ) {
			return;
		}

		$propertyDefinitions = $this->appFactory->getPropertyDefinitions();

		foreach ( $this->appFactory->getOption( 'sespSpecialProperties', array() ) as $key ) {

			if ( !$propertyDefinitions->deepHas( $key, 'id' ) ) {
				continue;
			}

			$property = new DIProperty(
				$propertyDefinitions->deepGet( $key, 'id' )
			);

			if ( $propertyDefinitions->isLocalDef( $key ) ) {
				$this->localPropertyAnnotator->addAnnotation( $property, $semanticData );
			} else {
				$this->findPropertyAnnotator( $property )->addAnnotation( $property, $semanticData );
			}
		}

		$this->appFactory->getLogger()->info(
			__METHOD__ . ' (procTime in sec: '. round( ( microtime( true ) - $time ), 5 ) . ')'
		);
	}

	/**
	 * @since 2.0
	 *
	 * @param DIProperty $property
	 *
	 * @return PropertyAnnotator
	 */
	public function findPropertyAnnotator( DIProperty $property ) {

		$key = $property->getKey();

		if ( $this->propertyAnnotators === array() ) {
			$this->initDefaultPropertyAnnotators();
		}

		if ( isset( $this->propertyAnnotators[$key] ) && is_callable( $this->propertyAnnotators[$key] ) ) {
			return call_user_func( $this->propertyAnnotators[$key], $this->appFactory );
		} elseif( isset( $this->propertyAnnotators[$key] ) ) {
			return $this->propertyAnnotators[$key];
		}

		return new NullPropertyAnnotator();
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 * @param Closure $callbak
	 */
	public function addPropertyAnnotator( $key, Closure $callback ) {
		$this->propertyAnnotators[$key] = $callback;
	}

	private function canAnnotate( $subject ) {

		if ( $subject === null || $subject->getTitle() === null || $subject->getTitle()->isSpecialPage() ) {
			return false;
		}

		$this->initDefaultPropertyAnnotators();

		return true;
	}

	private function initDefaultPropertyAnnotators() {

		$this->localPropertyAnnotator = new LocalPropertyAnnotator(
			$this->appFactory
		);

		// Encapsulate each instance to avoid direct instantiation for unused
		// matches
		$this->propertyAnnotators = array(

			CreatorPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new CreatorPropertyAnnotator( $appFactory );
			},

			PageViewsPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageViewsPropertyAnnotator( $appFactory );
			},

			UserRegistrationDatePropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new UserRegistrationDatePropertyAnnotator( $appFactory );
			},

			UserEditCountPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new UserEditCountPropertyAnnotator( $appFactory );
			},

			PageIDPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageIDPropertyAnnotator( $appFactory );
			},

			PageLengthPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageLengthPropertyAnnotator( $appFactory );
			},

			RevisionIDPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new RevisionIDPropertyAnnotator( $appFactory );
			},

			PageNumRevisionPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageNumRevisionPropertyAnnotator( $appFactory );
			},

			TalkPageNumRevisionPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new TalkPageNumRevisionPropertyAnnotator( $appFactory );
			},

			PageContributorsPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageContributorsPropertyAnnotator( $appFactory );
			},

			SubPagePropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new SubPagePropertyAnnotator( $appFactory );
			},

			ShortUrlPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new ShortUrlPropertyAnnotator( $appFactory );
			},

			ExifPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new ExifPropertyAnnotator( $appFactory );
			},

		);
	}

}
