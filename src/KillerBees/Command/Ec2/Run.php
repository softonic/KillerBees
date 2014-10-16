<?php
/**
 * StartCommand.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Command\Ec2;

use KillerBees\Amazon\Ec2;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;

/**
 * Ec2Instances.
 *
 * @author narcis.davins
 */
class Run extends Command
{
	public function __construct( Container $container )
	{
		$this->container = $container;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName( 'ec2:run' )
			->setDescription( 'Run Ec2 Instances' )
			->addOption( 'instances', 'i', InputOption::VALUE_REQUIRED, 'number of instances to create' )
		;
	}

	public function execute( InputInterface $input, OutputInterface $output )
	{
		$instances		= $input->getOption( 'instances' );

		if ( !isset( $instances ) )
		{
			throw new \InvalidArgumentException( 'The following options must be set: instances' );
		}

		$output->writeln( "<comment>Starting $instances instances</comment>" );

		$ec2 = $this->container->get( 'killerbees.ec2' );

		$amazon_config = $this->container->get( 'killerbees.configuration.amazon' )->get();

		try
		{
			$ec2->startInstances(
				$instances,
				$amazon_config['parameters']['amazon_key_name'],
				$amazon_config['parameters']['amazon_security_group'],
				$amazon_config['parameters']['amazon_instance_type']
			);
			
			$output->writeln( '<info>Instances successfully starting. They should be available in a few seconds</info>' );
		}
		catch( \Exception $e )
		{
			$output->writeln( "<error>There has been an error while trying to instantiate instances</error>\n{$e->getMessage()}" );
		}
	}
}

?>