<?php

namespace SESP\Tests\Unit;

use SESP\ObservableReporter;

/**
 * @covers \SESP\ObservableReporter
 *
 * @ingroup Test
 *
 * @group SESP
 * @group SESPExtension
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ObservableReporterTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SESP\ObservableReporter',
			new ObservableReporter
		);
	}

	public function testCallbackRegistration() {

		$validator = $this->getMockBuilder( '\stdClass' )
			->setMethods( array( 'assert' ) )
			->getMock();

		$validator->expects( $this->once() )
			->method( 'assert' )
			->with( $this->equalTo( 'Foo' ) );

		$reporter = function( $key, $value ) use( $validator ) {
			return $validator->assert( $key );
		};

		$observableReporter = new ObservableReporter;
		$observableReporter->registerCallback( $reporter );
		$observableReporter->reportStatus( 'Foo', true );
	}

}
