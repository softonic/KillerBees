<?php
/**
 * StartCommand.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Command\Generate;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;

/**
 * Generate url config skeleton.
 *
 * @author narcis.davins
 */
class Url extends Command
{
	public function __construct( Container $container = null )
	{
		$this->container = $container;
		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setName( 'generate:url' )
			->setDescription( 'Generate basic url config skeleton' )
			->addArgument( 'config-file', InputArgument::REQUIRED, 'url config skeleton file' )
		;
	}

	public function execute( InputInterface $input, OutputInterface $output )
	{
		$config_file = $input->getArgument( 'config-file' );
		if ( !$config_file )
		{
			throw new \InvalidArgumentException( 'config-name cannot be empty' );
		}

		if ( !is_dir( dirname( $config_file ) ) )
		{
			throw new \InvalidArgumentException( 'specify a valid directory where to create the config file' );
		}


		file_put_contents(
			$config_file,
			<<<URL_CONFIG
bootstrap_requests:
    - url: 'http://foo.bar'
      method: 'POST'
      headers:
        - Referer: 'https://foo.bar/hello'
          User-Agent: 'KillerBees'
      body: 'name=[user]&pwd=[pwd]'
      variable_sets:
        - user: 'user1'
          pwd: 'pwduser1'

        - user: 'user2'
          pwd: 'pwduser2'
requests:
    - url: 'http://url1'
      method: 'GET'
    - url: 'http://url2?id=[id]'
      method: 'GET'
      variable_sets:
        - id: 1
        - id: 2
        - id: 3
        - id: 4
        - id: 5
        - id: 6
shutdown_requests:
    - url: 'http://url/logout'
URL_CONFIG
		);
	}
}

?>