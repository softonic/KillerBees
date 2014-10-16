<?php
/**
 * Configuration.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration.
 *
 * @author narcis.davins
 */
class Amazon implements ConfigurationInterface
{
	const CONFIG = 'amazon.yml';

	public function get()
	{
		if ( !file_exists( __DIR__ . '/../../../config/'.self::CONFIG ) )
		{
			throw new \RuntimeException( 'Config file '.self::CONFIG.' does not exist. Please execute killerbees configure:amazon to create it' );
		}
		return $this->validateConfig( Yaml::parse( __DIR__ . '/../../../config/'.self::CONFIG ) );
	}

	public function getDefaults()
	{
		return $this->validateConfig( array() );
	}

	public function save( $config )
	{
		file_put_contents( __DIR__ . '/../../../config/'.self::CONFIG, Yaml::dump( $this->validateConfig( $config ) ) );
	}

	public function getConfigTreeBuilder()
	{
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root( 'amazon_parameters' );

		$rootNode
			->children()
				->scalarNode( 'amazon_ami' )->defaultValue( 'ami-ec6f1285' )->end()
				->scalarNode( 'amazon_instance_type' )->defaultValue( 't1.micro' )->end()
				->scalarNode( 'amazon_security_group' )->defaultValue( '' )->end()
				->scalarNode( 'amazon_key_name' )->defaultValue( '' )->end()
				->scalarNode( 'amazon_private_key_filepath' )->defaultValue( '' )->end()
				->scalarNode( 'amazon_public_key_filepath' )->defaultValue( '' )->end()
				->scalarNode( 'amazon_user' )->defaultValue( '' )->end()
				->arrayNode( 'amazon_credentials' )
					->addDefaultsIfNotSet()
					->children()
						->scalarNode( 'key' )
							->defaultValue( '' )
						->end()
						->scalarNode( 'secret' )
							->defaultValue( '' )
						->end()
						->scalarNode( 'default_cache_config' )
							->defaultValue( '' )
						->end()
						->booleanNode( 'certificate_authority' )
							->defaultFalse()
						->end()
					->end()
				->end()
			->end()
		;

		return $treeBuilder;
	}

	protected function validateConfig( $config )
	{
		$processor = new Processor();

		return array(
			'parameters' => $processor->processConfiguration(
				$this,
				$config
			)
		);
	}
}

?>