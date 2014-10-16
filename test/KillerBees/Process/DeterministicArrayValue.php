<?php

namespace KillerBees\Process;

class DeterministicArrayValue implements ArrayValueGetter
{
    public function get( $parameters )
    {
        return $parameters[0];
    }
}