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
class Terminate extends Command
{
	public function __construct( Container $container )
	{
		$this->container = $container;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName( 'ec2:terminate' )
			->setDescription( 'Terminate Ec2 Instances created with the given AMI' );
		;
	}

	public function execute( InputInterface $input, OutputInterface $output )
	{
		$output->writeln( "<comment>Stopping instances</comment>" );

		$ec2 = $this->container->get( 'killerbees.ec2' );
		try
		{
			$ec2->stopInstances();
			$output->writeln( '<info>Instances successfully terminating. They should be terminated in a few seconds</info>' );
		}
		catch( \Exception $e )
		{
			$output->writeln( "<error>There has been an error while trying to terminate instances</error>\n{$e->getMessage()}" );
		}
	}
}

?>