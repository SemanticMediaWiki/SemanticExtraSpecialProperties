<?php

namespace SESP\PropertyAnnotators;

use SESP\AppFactory;
use SESP\PropertyAnnotator;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\SemanticData;
use SMWContainerSemanticData as ContainerSemanticData;
use SMWDataItem as DataItem;
use SMWDIContainer as DIContainer;
use SMWDINumber as DINumber;
use User;
use Wikimedia\IPUtils;

/**
 * @private
 * @ingroup SESP
 *
 * @license GPL-2.0-or-later
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

	/** @var DIProperty DIProperty object for namespace number. */
	private static $nsProperty;
	/** @var DIProperty DIProperty object for number if edits in NS. */
	private static $editsProperty;

	/** @var AppFactory */
	private $appFactory;

	/**
	 * @param AppFactory $appFactory
	 */
	public function __construct( AppFactory $appFactory ) {
		$this->appFactory = $appFactory;
		self::$nsProperty = self::$nsProperty ?: new DIProperty( self::PROP_NS_ID );
		self::$editsProperty = self::$editsProperty ?: new DIProperty( self::PROP_CNT_ID );
	}

	/**
	 * {@inheritDoc}
	 */
	public function isAnnotatorFor( DIProperty $property ) {
		return $property->getKey() === self::PROP_ID;
	}

	/**
	 * @inheritDoc
	 */
	public function addAnnotation( DIProperty $property, SemanticData $semanticData ) {
		$subject = $semanticData->getSubject();
		$title = $subject->getTitle();

		if ( !$title->inNamespace( NS_USER ) ) {
			return;
		}

		$user = $this->appFactory->newUserFromTitle( $title );

		$id = null;
		$ip = null;
		if ( $user instanceof User && ( $count = $user->getEditCount() ) && is_int( $count ) ) {
			$id = $user->getID();
		} elseif ( IPUtils::isIPAddress( $title->getText() ) ) {
			$ip = $title->getText();
		} else {
			return;
		}
		$stats = $this->getEditsPerNs( $id, $ip );
		foreach ( $stats as $ns => $edits ) {
			$dataItem = self::container( $subject, $ns, $edits );
			if ( $dataItem instanceof DataItem ) {
				$semanticData->addPropertyObjectValue( $property, $dataItem );
			}
		}
	}

	/**
	 * Form a DIContainer holding namespace and number of edits.
	 *
	 * @param DIWikiPage $subject User page
	 * @param int $ns Namespace
	 * @param int $edits Number of edits
	 * @return DIContainer
	 */
	public static function container( DIWikiPage $subject, $ns, $edits ): DIContainer {
		$container = new ContainerSemanticData( new DIWikiPage(
			$subject->getDBkey(),
			$subject->getNamespace(),
			$subject->getInterwiki(),
			'_USEREDITCNTNS' . (string)$ns
		), false );
		$container->addPropertyObjectValue( self::$nsProperty, new DINumber( $ns ) );
		$container->addPropertyObjectValue( self::$editsProperty, new DINumber( $edits ) );
		return new DIContainer( $container );
	}

	/**
	 * @param int|null $id User ID (0 for anonymous users)
	 * @param string|null $ip Anonymous user's IP address
	 * @return int[] An associative array NS number => revision count
	 */
	private function getEditsPerNs( $id, $ip ): array {
		$db = $this->appFactory->getConnection();
		$queryTables = [ 'revision', 'actor', 'page' ];

		$joinConditions = [
			'page'	=> [ 'INNER JOIN', [ 'page_id=rev_page' ] ],
			'actor'	=> [ 'INNER JOIN', [ 'actor_id=rev_actor' ] ]
		];

		$result = $db->select(
		// FROM.
			$queryTables,
			// SELECT.
			[ 'ns' => 'page_namespace', 'edits' => 'COUNT(rev_id)' ],
			// WHERE.
			$id === null ? [ 'actor_name' => $ip ] : [ 'actor_user' => $id ],
			__METHOD__,
			// GROUP BY.
			[ 'GROUP BY' => [ 'page_namespace' ] ],
			// JOIN conditions.
			$joinConditions
		);

		$records = [];
		foreach ( $result as $row ) {
			$records[$row->ns] = $row->edits;
		}
		return $records;
	}
}
