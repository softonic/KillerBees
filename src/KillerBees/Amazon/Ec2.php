<?php
/**
 * Ec2.php.
 *
 * @package
 * @subpackage
 * @author narcis.davins
 */

namespace KillerBees\Amazon;

/**
 * Ec2.
 *
 * @author narcis.davins
 */
class Ec2
{
    const MICRO_INSTANCE = 't1.micro';

    public function __construct( $ami, $amazon_ec2_class, $aws_credentials )
    {
        \CFCredentials::set(array(
            'aws' => $aws_credentials
        ));

        $this->ami = $ami;
        $this->amazon_ec2_client = new $amazon_ec2_class;
    }

    public function listInstances( $attribute = 'dnsName' )
    {
        $response = $this->amazon_ec2_client->describe_instances( array(
                'Filter' => array(
                    array( 'Name' => 'image-id', 'Value' => $this->ami ),
                    // Only running instances.
                    array( 'Name' => 'instance-state-code', 'Value' => 16 ),
                )
            ) );

        if ( !$response->isOK() )
        {
            throw new \RuntimeException( 'Could not get Amazon EC2 instances: ' . json_encode( $response ) );
        }

        $hosts = array_map( function ( $value )
        {
            return (string) $value;
        }, $response->body->xpath( "reservationSet/item/instancesSet/item/$attribute" ) );

        return $hosts;
    }

    public function startInstances( $number_of_instances, $key_name, $security_group, $instance_type = self::MICRO_INSTANCE )
    {
        $response = $this->amazon_ec2_client->run_instances(
            $this->ami,
            $number_of_instances,
            $number_of_instances,
            array(
                'InstanceType' => $instance_type,
                'KeyName' => $key_name,
                'SecurityGroup' => $security_group
            )
        );

        if ( !$response->isOK() )
        {
            throw new \RuntimeException( 'Could not start Amazon EC2 instances: ' . json_encode( $response ) );
        }
    }

    public function stopInstances()
    {
        $instances_ids = $this->listInstances( 'instanceId' );
        $response = $this->amazon_ec2_client->terminate_instances( $instances_ids );
        
        if ( !$response->isOK() )
        {
            throw new \RuntimeException( 'Could not stop Amazon EC2 instances' . json_encode( $response ) );
        }
    }
}

?>