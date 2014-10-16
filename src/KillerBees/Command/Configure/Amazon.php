<?php
/**
 * StartCommand.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Command\Configure;

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
class Amazon extends Command
{
	const CONFIG = 'amazon.yml';

	public function __construct( Container $container = null )
	{
		$this->container = $container;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName( 'configure:amazon' )
			->setDescription( 'Configure KillerBees amazon parameters' )
		;
	}

	public function execute( InputInterface $input, OutputInterface $output )
	{
		$config_dir = __DIR__ . '/../../../../config';
		if ( !is_dir( $config_dir ) )
		{
			mkdir( $config_dir );
			$config_dir = realpath( $config_dir );
			$output->writeln( "Config dir: $config_dir created" );
		}

		$configuration = $this->container->get( 'killerbees.configuration.amazon' );
		try
		{
			$config_values = $configuration->get();
		}
		catch ( \RuntimeException $e )
		{
			$config_values = $configuration->getDefaults();
		}
		$configuration->save( $this->askValues( $output, $config_values ) );
	}

	protected function askValues( OutputInterface $output, $original_config, $accumulated_key = '' )
	{
		$accumulated_key = $accumulated_key ? "$accumulated_key." : '';
		foreach ( $original_config as $key => &$value )
		{
			if ( is_array( $value ) )
			{
				$value = $this->askValues( $output, $value, "$accumulated_key$key" );
			}
			else
			{
				$is_boolean = is_bool( $value );
				$dialog = $this->getHelperSet()->get( 'dialog' );
				$current_value = var_export( $value, true );
				$value = $dialog->askAndValidate(
					$output,
					"Please enter the value for: $accumulated_key$key (<comment>$current_value</comment>): ",
					function( &$answer ) use ( $is_boolean, $key ) {
						if ( false !== strpos( $key, 'path' ) && !file_exists( $answer ) )
						{
							throw new \InvalidArgumentException( 'file provided does not exist. Please ensure you provide a correct full path' );
						}
						return $answer;
					},
					false,
					$value
				);
				if ( $is_boolean )
				{
					$value = (bool)$value;
				}
			}
		}
		return $original_config;
	}

}

?>