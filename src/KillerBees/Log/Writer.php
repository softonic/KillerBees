<?php

namespace KillerBees\Log;

use Buzz\Message\Response;
use Buzz\Message\Request;
use Buzz\Client\Curl;

class Writer
{
    public function __construct( $log_file_handler )
    {
        $this->log_file_handler = $log_file_handler;
        $this->hostname         = gethostname();
    }

    public function write( $response, Request $request, Curl $curl )
    {
        $line = $this->formatLine( $response, $request, $curl );

        if ( flock( $this->log_file_handler, LOCK_EX ) )
        {
            fwrite( $this->log_file_handler, $line . "\n" );
            fflush( $this->log_file_handler );
            flock( $this->log_file_handler, LOCK_UN );
        }
        else
        {
            throw new \RuntimeException( "Couldn't get the lock when writing to log file" );
        }
    }

    private function formatLine( $response, Request $request, Curl $curl )
    {
        $line = "";

        $line .= $this->hostname;
        $line .= "\t";
        $line .= microtime( true );
        $line .= "\t";
        $line .= $request->getMethod();
        $line .= "\t";
        $line .= $request->getUrl();
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_EFFECTIVE_URL );
        $line .= "\t";
        $line .= $response ? $response->getStatusCode() : 0;
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_TOTAL_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_NAMELOOKUP_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_CONNECT_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_PRETRANSFER_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_STARTTRANSFER_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_REDIRECT_TIME );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_REDIRECT_COUNT );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_SIZE_UPLOAD );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_SIZE_DOWNLOAD );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_REQUEST_SIZE );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_CONTENT_LENGTH_DOWNLOAD );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_CONTENT_LENGTH_UPLOAD );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_SPEED_DOWNLOAD );
        $line .= "\t";
        $line .= $curl->getInfo( CURLINFO_SPEED_UPLOAD );
        $line .= "\t";

        return $line;
    }
}