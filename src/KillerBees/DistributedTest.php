<?php

declare( ticks = 1 );

namespace KillerBees;

use KillerBees\Amazon\Ec2;
use KillerBees\Log\ApacheBenchmarkProcessor;
use KillerBees\Ssh\MultiConnection;
use KillerBees\Ssh\Connection;

class DistributedTest
{
	public function __construct( $zip_archive, $connection )
	{
		$this->zip_archive 			= $zip_archive;
		$this->connection 			= $connection;
		$this->remote_tmp_path		= '/tmp/killerbees/';
		$this->log_file_path		= tempnam( sys_get_temp_dir(), 'killerbees_log_' ) . '.txt';
	}

	public function start()
	{
		echo "Uploading files...\n";

		$program_path = $this->uploadFiles();

		echo "Launching attacks...\n";
		echo "Log: {$this->log_file_path}\n";

		$this->installShutdownSignal();
		$output_stream = $this->launchAttack( $program_path );

		$this->appendToLog( $output_stream );
		$this->printOutput();
	}

	public function setForks( $n_forks )
	{
		$this->n_forks = $n_forks;
	}

	public function setRequestsPerFork( $n_requests_per_fork )
	{
		$this->n_requests_per_fork = $n_requests_per_fork;
	}

	public function setConfig( $config )
	{
		$this->config = $config;
		$this->remote_config = $this->remote_tmp_path . basename( $config );
	}

	private function launchAttack( $program_path )
	{
		return $this->connection->execute(
				$program_path . '/bin/killerbees'
				. ' attack:local -c'
				. escapeshellarg( $this->n_forks )
				. ' -r'
				. escapeshellarg( $this->n_requests_per_fork )
				. ' -f'
				. escapeshellarg( $this->remote_config )
				. ' 2>&1'
			)
			->getStdOutStream();
	}

	private function installShutdownSignal()
	{
		pcntl_signal( SIGINT, array( $this, 'handleShutdownSignal' ) );
	}

	public function handleShutdownSignal( $signal )
	{
		echo "Shutting down slaves...\n";
		$this->connection->execute( 'killall -9 killerbees' );

		exit( 0 );
	}

	private function uploadFiles()
	{
		$zip_path = $this->zipSelf();
		$remote_zip_path = $this->remote_tmp_path . basename( $zip_path );
		$destination_path = $remote_zip_path . '.dir';

		$this->connection->mkDir( $this->remote_tmp_path, 0777, true );
		$response = $this->connection->uploadFile( $zip_path, $remote_zip_path, 0777 );

		if ( !$response )
		{
			throw new \RuntimeException( 'Cannot upload file to remote server(s): ' . $zip_path . ' => ' . $remote_zip_path );
		}

		$this->connection->execute(
				'unzip -d ' .
				escapeshellarg( $destination_path ) .
				' ' .
				escapeshellarg( $remote_zip_path )
			)
			->readStdOut();

		$this->connection->execute(
				'chmod +x ' .
				escapeshellarg( $destination_path . '/bin/killerbees' )
			)
			->readStdOut();

		$response = $this->connection->uploadFile( $this->config, $this->remote_config, 0777, true );

		if ( !$response )
		{
			throw new \RuntimeException( 'Cannot upload config file to remote server(s): ' . $this->config . ' => ' . $this->remote_config );
		}

		return $destination_path;
	}

	private function zipSelf()
	{
		$base_path = realpath( __DIR__ . '/../..' );
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $base_path ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$zip_path = tempnam( sys_get_temp_dir(), 'killerbees_' ) . '.zip';
		$this->zip_archive->open( $zip_path, \ZipArchive::CREATE );

		foreach ( $iterator as $file )
		{
			$local_path = $file->getPathname();
			$archive_path = preg_replace( '/^' . preg_quote( $base_path, '/' ) . '/', '', $local_path );
			$archive_path = ltrim( $archive_path, '\\/' );

			if ( $file->isDir() )
			{
				$this->zip_archive->addEmptyDir( $archive_path );
			}
			else
			{
				$this->zip_archive->addFile( $local_path, $archive_path );
			}
		}

		$this->zip_archive->close();

		$hashed_zip_path = $zip_path . sha1_file( $zip_path ) . '.zip';
		rename( $zip_path, $hashed_zip_path );

		return $hashed_zip_path;
	}

	private function appendToLog( $output_stream )
	{
		$file_handle = fopen( $this->log_file_path, 'a' );
		stream_copy_to_stream( $output_stream, $file_handle );
		fclose( $file_handle );
	}

	private function printOutput()
	{
		$log_processor = new ApacheBenchmarkProcessor( $this->log_file_path );
		echo $log_processor->process();
	}
}