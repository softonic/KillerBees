<?php

namespace KillerBees\Process;

class Request
{
    public $url;
    public $method;
    public $headers;
    public $body;
    public $variable_sets;

    public function __construct( array $parameters )
    {
        $this->url              = $parameters['url'];
        $this->method           = isset( $parameters['method'] ) ? $parameters['method'] : 'GET';
        $this->headers          = isset( $parameters['headers'] ) ? $parameters['headers'] : array();
        $this->body             = isset( $parameters['body'] ) ? $parameters['body'] : '';
        $this->variable_sets    = isset( $parameters['variable_sets'] ) ? $parameters['variable_sets'] : array();
    }
}