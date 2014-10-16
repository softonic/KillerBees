<?php

namespace KillerBees\Log;

class ApacheBenchmarkProcessor implements Processor
{
    public function __construct( $file_path )
    {
        $this->file_path = $file_path;
    }

    public function process()
    {
        /*
        \s+50\%\s+([0-9]+)
        \s+90\%\s+([0-9]+)
        */

        $response = "";

        $response .= sprintf( "Time per request: %F [ms] (mean)\n", $this->getTimePerRequest() );
        $response .= sprintf( "Requests per second: %F [#/sec] (mean)\n", $this->getRequestsPerSecond() );
        $response .= sprintf( "Complete requests: %d\n", $this->getCompleteRequests() );
        $response .= sprintf( "50%% %d\n", $this->getTimeForPerecentage( .5 ) );
        $response .= sprintf( "66%% %d\n", $this->getTimeForPerecentage( .66 ) );
        $response .= sprintf( "75%% %d\n", $this->getTimeForPerecentage( .75 ) );
        $response .= sprintf( "80%% %d\n", $this->getTimeForPerecentage( .80 ) );
        $response .= sprintf( "90%% %d\n", $this->getTimeForPerecentage( .9 ) );
        $response .= sprintf( "95%% %d\n", $this->getTimeForPerecentage( .95 ) );
        $response .= sprintf( "98%% %d\n", $this->getTimeForPerecentage( .98 ) );
        $response .= sprintf( "99%% %d\n", $this->getTimeForPerecentage( .99 ) );
        $response .= sprintf( "100%% %d\n", $this->getTimeForPerecentage( 1 ) );

        return $response;
    }

    private function getTimePerRequest()
    {
        $request_times = $this->readColumn( 7 );

        if ( 0 == count( $request_times ) )
        {
            return null;
        }

        $request_times = array_map( function( $value ) {
            return (float) $value;
        }, $request_times );

        return array_sum( $request_times ) / count( $request_times ) * 1000;
    }

    private function getRequestsPerSecond()
    {
        $request_timestamps = $this->readColumn( 2 );

        if ( count( $request_timestamps ) <= 1 )
        {
            return null;
        }

        $request_timestamps = array_map( function( $value ) {
            return (float) $value;
        }, $request_timestamps );

        $first_request_timestamp = min( $request_timestamps );
        $last_request_timestamp = max( $request_timestamps );

        $average_interval = ( $last_request_timestamp - $first_request_timestamp ) / ( count( $request_timestamps ) - 1 );

        if ( $average_interval == 0 )
        {
            return null;
        }

        return 1 / $average_interval;
    }

    private function getCompleteRequests()
    {
        $status_codes = $this->readColumn( 6 );
        $status_codes = array_filter( $status_codes, function( $value )
        {
            return $value > 0;
        } );

        return count( $status_codes );
    }

    private function getTimeForPerecentage( $ratio )
    {
        if ( $ratio <= 0 )
        {
            return null;
        }

        $request_times = $this->readColumn( 7 );
        $request_times = array_map( function( $value ) {
            return (float) $value;
        }, $request_times );

        sort( $request_times );

        return $request_times[ceil( count( $request_times ) * $ratio ) - 1] * 1000;
    }

	private function readColumn( $index )
    {
        $handle     = fopen( $this->file_path, "r" );
        $values     = array();

        while ( ( $buffer = fgets( $handle ) ) !== false )
        {
            $columns    = explode( "\t", $buffer );
            $values[]   = @$columns[$index - 1];
        }
        if ( !feof( $handle ) )
        {
            throw new \RuntimeException( "Error: unexpected fgets() fail" );
        }

        return $values;
    }
}