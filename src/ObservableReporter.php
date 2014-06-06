<?php

namespace SESP;

/**
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 1.2.0
 */
class ObservableReporter {

	/** @var callable[] */
	protected $callbacks = array();

	/**
	 * @since  1.0
	 *
	 * @param mixed $key
	 * @param mixed $status
	 */
	public function reportStatus( $key, $status ) {
		foreach ( $this->callbacks as $callback ) {
			call_user_func_array( $callback, array( $key, $status ) );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param mixed|null $callback
	 */
	public function registerCallback( $callback = null ) {
		if ( is_callable( $callback ) ) {
			$this->callbacks[] = $callback;
		}
	}

}