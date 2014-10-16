<?php

namespace KillerBees;

class AggregateLineReaderTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		AggregateLineReader::register();
	}

	public function tearDown()
	{
		AggregateLineReader::unregister();
	}

	public function testReadingTwoAggregatedStreams()
	{
		$stream_1 = fopen( 'data://text/plain;base64,U29mdG9uaWM=', 'r' );
		$stream_2 = fopen( 'data://text/plain;base64,MTcxNA==', 'r' );

		$aggregate_filename = "aggregateread://" . uniqid();
		$handle = fopen( $aggregate_filename, 'r' );

		AggregateLineReader::addStream( $aggregate_filename, $stream_1 );
		AggregateLineReader::addStream( $aggregate_filename, $stream_2 );

		$stream_contents = stream_get_contents( $handle );

		$this->assertContains( 'Softonic', $stream_contents );
		$this->assertContains( '1714', $stream_contents );
	}
}
