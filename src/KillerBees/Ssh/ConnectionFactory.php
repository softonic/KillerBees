<?php

namespace KillerBees\Ssh;

use KillerBees\Amazon\Ec2;

class ConnectionFactory
{
	private $private_key;

	private $public_key;

	private $user;

	public function __construct( Ec2 $ec2, $connection_class, $user, $private_key, $public_key )
	{
		$this->user = $user;
		$this->public_key = $public_key;
		$this->private_key = $private_key;
		$this->ec2 				= $ec2;
		$this->connection_class = $connection_class;
	}

	public function getConnectionsForAllInstances()
	{
		$instances = $this->ec2->listInstances();
		$connections = array();

		foreach ( $instances as $instance )
		{
			$connections[] = new $this->connection_class(
				$instance,
				22,
				$this->user,
				$this->private_key,
				$this->public_key
			);
		}

		return $connections;
	}
}