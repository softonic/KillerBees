<?php

namespace KillerBees\Process;

use Buzz\Browser;
use Buzz\Client\Curl;
use KillerBees\Log\Writer;

class RequestAction implements Action
{
    private $browser;

    public function __construct( Browser $browser )
    {
        $this->browser      = $browser;
        $this->log_writer   = new Writer( fopen( 'php://stdout', 'w' ) );
    }

    public function execute()
    {
        // Smooth the starting impact.
        sleep( mt_rand( 0, 30 ) );

        $cookie_file = tempnam( sys_get_temp_dir(), 'cookie.txt' );
        $client = $this->browser->getClient();
        $client->setOption( CURLOPT_COOKIEFILE, $cookie_file );
        $client->setOption( CURLOPT_COOKIEJAR, $cookie_file );

        if ( !empty( $this->config['bootstrap_requests'] ) )
        {
            foreach ( $this->config['bootstrap_requests'] as $bootstrap_request )
            {
                $response = $this->doRequest( $bootstrap_request );
                $this->log_writer->write( $response, $this->browser->getLastRequest(), $client );
            }
        }

        for( $i = $this->n_requests_per_fork; $i; --$i )
        {
            $request_calculator = new RequestCalculator();
            $request            = $request_calculator->calculateRequestUri( $this->config );
            $response           = $this->doRequest( $request );
            $this->log_writer->write( $response, $this->browser->getLastRequest(), $client );
        }

        @unlink( $cookie_file );
    }

    public function setRequestsPerFork( $n_requests_per_fork )
    {
        $this->n_requests_per_fork = $n_requests_per_fork;
    }

    public function setConfig( array $config )
    {
        $this->config = $config;
    }

    private function doRequest( $request )
    {
        try
        {
            $response = $this->browser->call(
                $request->url,
                $request->method,
                $request->headers,
                $request->body
            );

            return $response;
        }
        catch ( \Exception $e )
        {
            return null;
        }
    }

    public static function replaceVariables( $url, array $variable_set )
    {
        // Surrounding keys with brackets.
        $variable_set = array_combine( array_map( function( $key )
        {
            return '[' . $key . ']';
        }, array_keys( $variable_set ) ), array_values( $variable_set ) );

        return strtr( $url, $variable_set );
    }
}