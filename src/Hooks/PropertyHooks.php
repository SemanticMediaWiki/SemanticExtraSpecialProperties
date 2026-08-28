<?php

namespace SESP\Hooks;

use SESP\ExtraPropertyAnnotator;
use SESP\PropertyRegistry;
use SMW\DataModel\SemanticData;
use SMW\PropertyRegistry as Registry;
use SMW\Store;

/**
 * Registers the Semantic Extra Special Properties and annotates entities with
 * their values when Semantic MediaWiki stores data.
 *
 * @license GPL-2.0-or-later
 * @since 5.0.0
 */
class PropertyHooks {

	public function __construct(
		private readonly PropertyRegistry $propertyRegistry,
		private readonly ExtraPropertyAnnotator $extraPropertyAnnotator
	) {
	}

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Property::initProperties
	 * @since 5.0.0
	 */
	public function onSMW__Property__initProperties( Registry $registry ): bool {
		return $this->propertyRegistry->register( $registry );
	}

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::SQLStore::AddCustomFixedPropertyTables
	 * @since 5.0.0
	 *
	 * @param array &$customFixedProperties
	 * @param array &$fixedPropertyTablePrefix
	 */
	public function onSMW__SQLStore__AddCustomFixedPropertyTables(
		array &$customFixedProperties,
		&$fixedPropertyTablePrefix
	): bool {
		$this->propertyRegistry->registerFixedProperties(
			$customFixedProperties,
			$fixedPropertyTablePrefix
		);

		return true;
	}

	/**
	 * @see https://www.semantic-mediawiki.org/wiki/Hooks/SMW::Store::BeforeDataUpdateComplete
	 * @since 5.0.0
	 */
	public function onSMW__Store__BeforeDataUpdateComplete( Store $store, SemanticData $semanticData ): bool {
		$this->extraPropertyAnnotator->addAnnotation( $semanticData );

		return true;
	}
}
