<?php

namespace KillerBees;

use Symfony\Component\Yaml\Yaml;
use KillerBees\Process\Forker;
use KillerBees\Process\Action;

class Test
{
	public function __construct( Action $action )
    {
        $this->action = $action;
    }

	public function start()
	{
		$forker = new Forker( $this->n_forks );

		$this->action->setRequestsPerFork( $this->n_requests_per_fork );
		$this->action->setConfig( $this->loadConfig( $this->config ) );

		$forker->registerChildAction( $this->action );
		$forker->start();
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
	}

	private function loadConfig( $config )
	{
		if ( !file_exists( $config ) )
		{
			throw new \RuntimeException( "Can't load config file, file does not exist" );
		}

		return Yaml::parse( file_get_contents( $config ) );
	}
}