<?php

namespace SESP\Tests;

use SESP\ExtraPropertyAnnotator;
use SESP\PropertyDefinitions;
use SMW\DataItems\WikiPage;
use SMW\DataModel\SemanticData;
use SESP\AppFactory;
use SESP\LabelFetcher;
use SESP\PropertyAnnotator;
/**
 * @covers \SESP\ExtraPropertyAnnotator
 * @group semantic-extra-special-properties
 *
 * @license GPL-2.0-or-later
 * @since 2.0
 *
 * @author mwjames
 */
class ExtraPropertyAnnotatorTest extends \PHPUnit\Framework\TestCase {

	private $appFactory;

	protected function setUp(): void {
		parent::setUp();

		$this->appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->getMock();
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			ExtraPropertyAnnotator::class,
			new ExtraPropertyAnnotator( $this->appFactory )
		);
	}

	public function testaddAnnotationOnInvalidSubject() {
		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->never() )
			->method( 'addPropertyObjectValue' );

		$instance = new ExtraPropertyAnnotator(
			$this->appFactory
		);

		$instance->addAnnotation( $semanticData );
	}

	public function testAddAnnotationOnLocalDef() {
		$appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getPropertyDefinitions', 'getOption' ] )
			->getMock();

		$subject = WikiPage::newFromText( __METHOD__ );

		$callback = static function ( $appFactory, $property, $semanticData ) {
			return $semanticData->getSubject();
		};

		$localPropertyDefinitions['FAKE_PROP'] = [
			'id'    => 'FAKE_PROP',
			'callback' => $callback
		];

		$labelFetcher = $this->getMockBuilder( LabelFetcher::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyDefinitions = new PropertyDefinitions(
			$labelFetcher
		);

		$propertyDefinitions->setLocalPropertyDefinitions(
			$localPropertyDefinitions
		);

		$appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->willReturn( $propertyDefinitions );

		$appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( 'sespgEnabledPropertyList' )
			->willReturn( $localPropertyDefinitions );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$instance = new ExtraPropertyAnnotator(
			$appFactory
		);

		$instance->addAnnotation( $semanticData );
	}

	public function testAddAnnotationOnPredefined() {
		$appFactory = $this->getMockBuilder( AppFactory::class )
			->disableOriginalConstructor()
			->onlyMethods( [ 'getPropertyDefinitions', 'getOption' ] )
			->getMock();

		$subject = WikiPage::newFromText( __METHOD__ );

		$specialProperties = [ 'FAKE2' ];

		$defs['FAKE2'] = [
			'id'    => 'FAKE2'
		];

		$labelFetcher = $this->getMockBuilder( LabelFetcher::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyDefinitions = new PropertyDefinitions(
			$labelFetcher
		);

		$propertyDefinitions->setPropertyDefinitions(
			$defs
		);

		$appFactory->expects( $this->once() )
			->method( 'getPropertyDefinitions' )
			->willReturn( $propertyDefinitions );

		$appFactory->expects( $this->once() )
			->method( 'getOption' )
			->with( 'sespgEnabledPropertyList' )
			->willReturn( $specialProperties );

		$semanticData = $this->getMockBuilder( SemanticData::class )
			->disableOriginalConstructor()
			->getMock();

		$semanticData->expects( $this->once() )
			->method( 'getSubject' )
			->willReturn( $subject );

		$propertyAnnotator = $this->getMockBuilder( PropertyAnnotator::class )
			->disableOriginalConstructor()
			->getMock();

		$propertyAnnotator->expects( $this->once() )
			->method( 'addAnnotation' );

		$instance = new ExtraPropertyAnnotator(
			$appFactory
		);

		$instance->addPropertyAnnotator( 'FAKE2', $propertyAnnotator );

		$instance->addAnnotation( $semanticData );
	}

}
