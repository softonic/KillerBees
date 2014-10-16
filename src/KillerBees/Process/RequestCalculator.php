<?php

namespace KillerBees\Process;

class RequestCalculator
{
    private $browser;

    public function __construct( ArrayValueGetter $random_array_value = null )
    {
        $this->random_array_value = $random_array_value ? : new RandomArrayValue();
    }

    public function calculateRequestUri( array $config )
    {
        $request = $this->selectRandomRequestFromConfig( $config['requests'] );

        if ( !empty( $request->variable_sets ) )
        {
            $variable_set      = $this->selectRandomVariableSet( $request );
            $request->url      = $this->replaceVariables( $request->url, $variable_set );
            $request->body     = $this->replaceVariables( $request->body, $variable_set );
            $request->headers  = array_map( function( $value ) use ( $variable_set )
            {
                return $this->replaceVariables( $value, $variable_set );
            }, $request->headers );
        }

        if ( !empty( $config['hosts'] ) )
        {
            $request->url = $this->selectRandomHost( $config['hosts'] ) . $request->url;
        }

        return $request;
    }

    private function selectRandomHost( $hosts )
    {
        return $this->random_array_value->get( $hosts );
    }

    private function selectRandomRequestFromConfig( $requests_config )
    {
        return new Request( $this->random_array_value->get( $requests_config ) );
    }

    private function selectRandomVariableSet( $request )
    {
        return $this->random_array_value->get( $request->variable_sets );
    }

    private function replaceVariables( $value_with_placeholders, array $variable_set )
    {
        // Surrounding keys with brackets.
        $variable_set = array_combine( array_map( function( $key )
        {
            return '[' . $key . ']';
        }, array_keys( $variable_set ) ), array_values( $variable_set ) );

        return strtr( $value_with_placeholders, $variable_set );
    }
}