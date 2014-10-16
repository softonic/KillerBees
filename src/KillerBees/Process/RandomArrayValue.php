<?php

namespace KillerBees\Process;

class RandomArrayValue implements ArrayValueGetter
{
    public function get( $parameters )
    {
        return $parameters[array_rand( $parameters )];
    }
}