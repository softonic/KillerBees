<?php

namespace KillerBees\Ssh;

use KillerBees\AggregateLineReader;

class MultiConnection
{
    private $connections;

    public function __construct( ConnectionFactory $connection_factory )
    {
        $this->connection_factory = $connection_factory;
    }

    public function execute( $command )
    {
        foreach ( $this->getConnections() as $connection )
        {
            $connection->execute( $command );
        }
        return $this;
    }

    public function readStdOut()
    {
        $responses  = array();

        foreach ( $this->getConnections() as $index => $connection )
        {
            $responses[$index] = $connection->readStdOut();
        }

        return $responses;
    }

    public function getStdOutStream()
    {
        AggregateLineReader::register();

        $aggregate_filename = "aggregateread://" . uniqid();
        $handle = fopen( $aggregate_filename, 'r' );

        foreach ( $this->getConnections() as $connection )
        {
            AggregateLineReader::addStream( $aggregate_filename, $connection->getStdOutStream() );
        }

        return $handle;
    }

    public function mkDir( $name, $mode = 0777, $recursive = false )
    {
        $responses = array();

        foreach ( $this->getConnections() as $index => $connection )
        {
            $responses[$index] = $connection->mkDir( $name, $mode, $recursive );
        }

        return array_reduce( $responses, function( $value_a, $value_b )
        {
            return $value_a && $value_b;
        }, true );
    }

    public function uploadFile( $local_file, $remote_file, $create_mode = 0644, $overwrite = false )
    {
        $responses = array();

        foreach ( $this->getConnections() as $index => $connection )
        {
            $responses[$index] = $connection->uploadFile( $local_file, $remote_file, $create_mode, $overwrite );
        }

        return array_reduce( $responses, function( $value_a, $value_b )
        {
            return $value_a && $value_b;
        }, true );
    }

    public function getConnections()
    {
        if ( !isset( $this->connections ) )
        {
            $this->connections = $this->connection_factory->getConnectionsForAllInstances();
            
        }
        return $this->connections;
    }
}