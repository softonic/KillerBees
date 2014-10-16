<?php

namespace KillerBees\Process;

class RequestCalculatorTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->request_action = new \KillerBees\Process\RequestCalculator( new DeterministicArrayValue() );
	}

	/**
	 * @test
	 */
	public function it_returns_a_request_from_the_list()
	{
		$config = array(
			'requests' => array(
				array(
					'method' 	=> 'GET',
					'url' 		=> 'http://domain.tld'
				),
				array(
					'method' 	=> 'POST',
					'url' 		=> 'http://domain_post.tld'
				),
			)
		);

		$expected_request 	= new Request( array( 'url' => 'http://domain.tld' ) );
		$actual_request 	= $this->request_action->calculateRequestUri( $config );
        $this->assertEquals( $actual_request->url, $expected_request->url, 'Must return one of the requests' );
        $this->assertEquals( $actual_request->method, $expected_request->method, 'Must return one of the requests' );
	}

	/**
	 * @test
	 */
	public function it_returns_request_and_replaces_one_placeholder_with_variable()
	{
		$config = array(
			'requests' => array(
				array(
					'method' 		=> 'POST',
					'url' 			=> 'http://[host]/users',
					'variable_sets' => array(
						array(
							'host' => 'domain.tld'
						)
					)
				),
				array(
					'method' 	=> 'POST',
					'url' 		=> 'post_url'
				),
			)
		);

		$expected_request 	= new Request( array( 'method' => 'POST', 'url' => 'http://domain.tld/users' ) );
        $actual_request 	= $this->request_action->calculateRequestUri( $config );

        $this->assertEquals( $actual_request->url, $expected_request->url, 'Must return a request with placeholders replaced by variables' );
        $this->assertEquals( $actual_request->method, $expected_request->method, 'The method of the request must be right' );
	}

	/**
	 * @test
	 */
	public function it_replaces_several_placeholders_with_variables()
	{
		$config = array(
			'requests' => array(
				array(
					'method' 		=> 'GET',
					'url' 			=> 'http://[host]/users/[id]',
					'variable_sets' => array(
						array(
							'host' 	=> 'domain.tld',
							'id'	=> 23
						)
					)
				),
				array(
					'method' 	=> 'POST',
					'url' 		=> 'post_url'
				),
			)
		);

		$expected_request 	= new Request( array( 'url' => 'http://domain.tld/users/23' ) );
        $actual_request 	= $this->request_action->calculateRequestUri( $config );
        $this->assertEquals( $actual_request->url, $expected_request->url, 'Must return the chosen url with placeholders replaced by variables' );
        $this->assertEquals( $actual_request->method, $expected_request->method, 'Must return the same method' );
	}

	/**
	 * @test
	 */
	public function it_mixes_the_urls_with_the_different_hosts()
	{
		$config = array(
			'hosts' 	=> array(
				'http://domain1.tld',
				'http://domain2.tld',
				'http://domain3.tld',
			),
			'requests' 	=> array(
				array(
					'method' 		=> 'GET',
					'url' 			=> '/users/[id]',
					'variable_sets' => array(
						array(
							'id'	=> 23
						)
					)
				),
				array(
					'method' 	=> 'POST',
					'url' 		=> '/post_url'
				),
			)
		);

		$expected_request 	= new Request( array( 'url' => 'http://domain1.tld/users/23' ) );
        $actual_request 	= $this->request_action->calculateRequestUri( $config );
        $this->assertEquals( $actual_request->url, $expected_request->url, 'Must return the chosen url with placeholders replaced by variables' );
	}

}