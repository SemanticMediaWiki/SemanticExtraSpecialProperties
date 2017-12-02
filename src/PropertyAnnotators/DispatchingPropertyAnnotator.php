<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SESP\PropertyAnnotator;
use SESP\AppFactory;
use Closure;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class DispatchingPropertyAnnotator implements PropertyAnnotator {

	/**
	 * @var AppFactory
	 */
	private $appFactory;

	/**
	 * @var PropertyAnnotator[]
	 */
	private $propertyAnnotators = [];

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
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return true;
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 * @param PropertyAnnotator $propertyAnnotator
	 */
	public function addPropertyAnnotator( $key, PropertyAnnotator $propertyAnnotator ) {
		$this->propertyAnnotators[$key] = $propertyAnnotator;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		$this->findPropertyAnnotator( $property )->addAnnotation( $property, $semanticData );
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

		if ( $this->propertyAnnotators === [] ) {
			$this->initDefaultPropertyAnnotators();
		}

		if ( isset( $this->propertyAnnotators[$key] ) && is_callable( $this->propertyAnnotators[$key] ) ) {
			return call_user_func( $this->propertyAnnotators[$key], $this->appFactory );
		} elseif( isset( $this->propertyAnnotators[$key] ) ) {
			return $this->propertyAnnotators[$key];
		}

		return new NullPropertyAnnotator();
	}

	private function initDefaultPropertyAnnotators() {

		// Encapsulate each instance to avoid direct instantiation for unused
		// matches
		$this->propertyAnnotators = [

			CreatorPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new CreatorPropertyAnnotator( $appFactory );
			},

			PageViewsPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new PageViewsPropertyAnnotator( $appFactory );
			},

			ApprovedRevPropertyAnnotator::PROP_ID => function( $appFactory ) {
				return new ApprovedRevPropertyAnnotator( $appFactory );
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

		];
	}

}
