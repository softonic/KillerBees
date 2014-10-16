<?php

namespace KillerBees\Ssh;

class Connection
{
    private $connection;

    public function __construct( $host, $port, $user, $private_key, $public_key )
    {
        $this->host         = $host;
        $this->port         = $port;
        $this->user         = $user;
        $this->private_key  = $private_key;
        $this->public_key   = $public_key;
    }

    public function execute( $command )
    {
        if ( !$this->connection )
        {
            $this->connect();
        }

        $this->stdout_stream = ssh2_exec( $this->connection, $command );

        if ( false === $this->stdout_stream )
        {
            throw new \RuntimeException( 'Error when executing command: ' . $command  . ' on host ' . $this->host );
        }

        $this->stderr_stream = ssh2_fetch_stream( $this->stdout_stream, SSH2_STREAM_STDERR );

        stream_set_blocking( $this->stdout_stream, true );
        stream_set_blocking( $this->stderr_stream, false );

        return $this;
    }

    public function readStdOut()
    {
        return stream_get_contents( $this->stdout_stream );
    }

    public function readStdErr()
    {
        return stream_get_contents( $this->stderr_stream );
    }

    public function getStdOutStream()
    {
        return $this->stdout_stream;
    }

    public function getStdErrStream()
    {
        return $this->stderr_stream;
    }

    public function mkDir( $name, $mode = 0777, $recursive = false )
    {
        if ( !$this->connection )
        {
            $this->connect();
        }

        $sftp = ssh2_sftp( $this->connection );
        return ssh2_sftp_mkdir( $sftp, $name, $mode, $recursive );
    }

    public function uploadFile( $local_file, $remote_file, $create_mode = 0644, $overwrite = false )
    {
        if ( !$this->connection )
        {
            $this->connect();
        }

        if ( !$overwrite && @ssh2_sftp_stat( $this->connection, $remote_file ) )
        {
            return;
        }

        return ssh2_scp_send( $this->connection, $local_file , $remote_file, $create_mode );
    }

    private function connect()
    {
        $this->connection = ssh2_connect( $this->host, $this->port, array(
            'hostkey' => 'ssh-rsa'
        ) );

        if ( !ssh2_auth_pubkey_file( $this->connection, $this->user, $this->public_key, $this->private_key ) )
        {
            $this->connection = null;
            throw new \RuntimeException( 'Can\'t authenticate to host ' . $this->host );
        }
    }
}