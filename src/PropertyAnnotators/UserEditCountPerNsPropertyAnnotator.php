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
use Wikimedia\IPUtils;

/**
 * @private
 * @ingroup SESP
 *
 * @license GNU GPL v2+
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

	/** @var DIProperty $nsProperty DIProperty object for namespace number. */
	private static $nsProperty;
	/** @var DIProperty $editsProperty DIProperty object for number if edits in NS. */
	private static $editsProperty;

	/** @var AppFactory $appFactory */
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
	 * @param int|null $id User ID (null for anonymous users)
	 * @param string|null $ip Anonymous user's IP address
	 * @return int[] An associative array NS number => revision count
	 */
	private function getEditsPerNs( $id, $ip ): array {
		$db = $this->appFactory->getConnection();
		$result = $db->select(
			[ 'revision', 'revision_actor_temp', 'actor', 'page' ], // FROM.
			[ 'ns' => 'page.page_namespace', 'edits' => 'COUNT(revision.rev_id)' ], // SELECT.
			$id === null ? [ 'actor.actor_name' => $ip ] : [ 'actor.actor_user' => $id ], // WHERE.
			__METHOD__,
			[ 'GROUP BY' => [ 'page.page_namespace' ] ], // GROUP BY.
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

