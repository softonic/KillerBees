<?php
/**
 * Attack.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Command\Attack;

use KillerBees\Amazon\Ec2;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;

use KillerBees\DistributedTest;

/**
 * Attack.
 *
 * @author narcis.davins
 */
class Distributed extends Command
{
	public function __construct( Container $container )
	{
		$this->container = $container;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName( 'attack:distributed' )
			->setDescription( 'Launch an attack distributed on multiple amazon ec2 instances matching the given AMI' )
			->addOption( 'requests', 'r', InputOption::VALUE_REQUIRED, 'number of requests to do per fork' )
			->addOption( 'config-file', 'f', InputOption::VALUE_REQUIRED, 'url configuration file' )
			->addOption( 'concurrent-requests', 'c', InputOption::VALUE_REQUIRED, 'total concurrent requests' )
		;
	}

	protected function execute( InputInterface $input, OutputInterface $output )
	{
		$n_forks				= intval( $input->getOption( 'concurrent-requests' ) );
		$n_requests_per_fork	= intval( $input->getOption( 'requests' ) );
		$config					= $input->getOption( 'config-file' );

		if ( !$n_forks || !$n_requests_per_fork || empty( $config ) )
		{
			throw new \InvalidArgumentException( 'config, concurrent-requests and requests options are not properly set' );
		}

		if ( !file_exists( $config ) )
		{
			throw new \InvalidArgumentException( "config-file: '$config' does not exist. Specify a valid config filename" );
		}

		$test = $this->container->get( 'killerbees.distributed_test' );

		$test->setForks( $n_forks );
		$test->setRequestsPerFork( $n_requests_per_fork );
		$test->setConfig( $config );

		$test->start();
	}
}

?>