<?php

namespace SESP\PropertyAnnotators;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWDataItem as DataItem;
use SESP\PropertyAnnotator;
use SESP\AppFactory;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author mwjames
 */
class LocalPropertyAnnotator implements PropertyAnnotator {

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
		return true;
	}

	/**
	 * @since 2.0
	 *
	 * {@inheritDoc}
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {

		$time = microtime( true );

		$localDefs = $this->appFactory->getOption( 'sespgLocalDefinitions', [] );

		foreach ( $localDefs as $key => $definition ) {
			$this->callOnLocalDef( $definition, $property, $semanticData );
		}

		$this->appFactory->getLogger()->info(
			__METHOD__ . ' (procTime in sec: '. round( ( microtime( true ) - $time ), 5 ) . ')'
		);
	}

	private function callOnLocalDef( $definition, $property, $semanticData ) {

		if ( !isset( $definition['id'] ) || $definition['id'] !== $property->getKey() ) {
			return;
		}

		$dataItem = null;

		if ( isset( $definition['callback'] ) && is_callable( $definition['callback'] ) ) {
			$dataItem = call_user_func_array( $definition['callback'], [ $this->appFactory, $property, $semanticData ] );
		}

		if ( $dataItem instanceof DataItem ) {
			$semanticData->addPropertyObjectValue( $property, $dataItem );
		}
	}

}
