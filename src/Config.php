<?php

namespace SESP;

use MediaWiki\Config\GlobalVarConfig;

class Config extends GlobalVarConfig {

	public function __construct() {
		parent::__construct( 'sespg' );
	}

	/**
	 * Factory method for MediaWikiServices
	 * @return Config
	 */
	public static function newInstance() {
		return new self();
	}
}
