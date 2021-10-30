<?php

namespace SESP\PropertyAnnotators;

use SESP\PropertyAnnotator;
use SESP\AppFactory;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWContainerSemanticData as ContainerSemanticData;
use SMWDataItem as DataItem;
use SMWDIContainer as DIContainer;
use SMWDINumber as DINumber;
use User;


/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
 * @since 2.0
 *
 * @author Alexander Mashin
 */
class UserEditCountPerNsPropertyAnnotator implements PropertyAnnotator {

	/**
	 * Predefined property IDs
	 */
	/** @const string PROP_ID ID for the whole record. */
	public const PROP_ID = '___USEREDITCNTNS';
	/** @const string PROP_NS_ID ID for the NS number in the record. */
	private const PROP_NS_ID = '___USEREDITCNTNS_NS';
	/** @const string PROP_CNT_ID ID for the edit count in the record. */
	private const PROP_CNT_ID = '___USEREDITCNTNS_CNT';

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
	 * @inheritDoc
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {

		$title = $semanticData->getSubject()->getTitle();

		if ( !$title->inNamespace( NS_USER ) ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle( $title );

		if ( $user instanceof User && ( $count = $user->getEditCount() ) && is_int( $count ) ) {
			$id = $user->getID();
			$stats = $this->getEditsPerNs( $id, $title->getText() );
			$subject = $semanticData->getSubject();
			$nsProperty = new DIProperty( self::PROP_NS_ID );
			$editsProperty = new DIProperty( self::PROP_CNT_ID );
			foreach ( $stats as $ns => $edits ) {
				$page = new DIWikiPage(
					$subject->getDBkey(),
					$subject->getNamespace(),
					$subject->getInterwiki(),
					'_USEREDITCNTNS' . (string)$ns
				);
				$containerSemanticData = new ContainerSemanticData( $page, false );
				$nsDataItem = new DINumber( $ns );
				$containerSemanticData->addPropertyObjectValue( $nsProperty, $nsDataItem );
				$editsDataItem = new DINumber( $edits );
				$containerSemanticData->addPropertyObjectValue( $editsProperty, $editsDataItem );
				$dataItem = new DIContainer( $containerSemanticData );
				if ( $dataItem instanceof DataItem ) {
					$semanticData->addPropertyObjectValue( $property, $dataItem );
				}
			}
		}
	}

	/**
	 * @param int $userId User ID (0 for anonymous users)
	 * @param string $ip Anonymous user's IP address
	 * @return int[] An associative array NS number => revision count
	 */
	private function getEditsPerNs( $userId, $ip ): array {
		$db = $this->appFactory->getConnection();
		$result = $db->select(
			[ 'revision', 'revision_actor_temp', 'actor', 'page' ], // FROM.
			[ 'ns' => 'page.page_namespace', 'edits' => 'COUNT(revision.rev_id)' ],
			$userId === 0 ? [ 'actor.actor_name' => $ip ] : [ 'actor.actor_user' => $userId ], // WHERE.
			__METHOD__,
			[ 'GROUP BY' => [ 'page.page_namespace' ] ],
			[ // JOIN conditions.
				'page'					=> [ 'INNER JOIN', ['page.page_id=revision.rev_page'] ],
				'revision_actor_temp'	=> [ 'INNER JOIN', ['revision_actor_temp.revactor_rev=revision.rev_id'] ],
				'actor'					=> [ 'INNER JOIN', ['actor.actor_id=revision_actor_temp.revactor_actor'] ]
			]
		);
		$records = [];
		foreach ( $result as $row ) {
			$records[$row->ns] = $row->edits;
		}
		return $records;
	}
}
