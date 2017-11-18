<?php

namespace SESP;

use SMW\DIProperty;
use SMW\SemanticData;
use SESP\PropertyAnnotators\LocalPropertyAnnotator;
use SESP\PropertyAnnotators\DispatchingPropertyAnnotator;

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
	 * @var DispatchingPropertyAnnotator
	 */
	private $dispatchingPropertyAnnotator;

	/**
	 * @var LocalPropertyAnnotator
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

		foreach ( $this->appFactory->getOption( 'sespgEnabledPropertyList', [] ) as $key ) {

			if ( !$propertyDefinitions->deepHas( $key, 'id' ) ) {
				continue;
			}

			$property = new DIProperty(
				$propertyDefinitions->deepGet( $key, 'id' )
			);

			if ( $propertyDefinitions->isLocalDef( $key ) ) {
				$this->localPropertyAnnotator->addAnnotation( $property, $semanticData );
			} else {
				$this->dispatchingPropertyAnnotator->addAnnotation( $property, $semanticData );
			}
		}

		$this->appFactory->getLogger()->info(
			__METHOD__ . ' (procTime in sec: '. round( ( microtime( true ) - $time ), 5 ) . ')'
		);
	}

	/**
	 * @since 2.0
	 *
	 * @param string $key
	 * @param PropertyAnnotator $propertyAnnotator
	 */
	public function addPropertyAnnotator( $key, PropertyAnnotator $propertyAnnotator ) {

		if ( $this->dispatchingPropertyAnnotator === null ) {
			$this->initPropertyAnnotators();
		}

		$this->dispatchingPropertyAnnotator->addPropertyAnnotator( $key, $propertyAnnotator );
	}

	private function canAnnotate( $subject ) {

		if ( $subject === null || $subject->getTitle() === null || $subject->getTitle()->isSpecialPage() ) {
			return false;
		}

		if ( $this->dispatchingPropertyAnnotator === null ) {
			$this->initPropertyAnnotators();
		}

		return true;
	}

	private function initPropertyAnnotators() {

		$this->localPropertyAnnotator = new LocalPropertyAnnotator(
			$this->appFactory
		);

		$this->dispatchingPropertyAnnotator = new DispatchingPropertyAnnotator(
			$this->appFactory
		);
	}

}
