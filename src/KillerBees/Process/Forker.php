<?php

namespace KillerBees\Process;

declare( ticks = 1 );

class Forker
{
    private $n_forks;
    private $children = array();
    private $child_actions = array();

    public function __construct( $n_forks )
    {
        $this->n_forks = $n_forks;
    }

    public function start()
    {
        $this->forkChildren( $this->n_forks );
    }

    public function registerChildAction( Action $action )
    {
        $this->child_actions[] = $action;
    }

    private function forkChildren( $n_forks )
    {
        if ( 0 >= $n_forks ) return;


        for ( $slot = 0; $slot < $n_forks; ++$slot )
        {
            $pid = $this->fork();

            if ( $pid )
            {
                $this->children[$slot] = $pid;
            }
            else
            {
                $this->child_actions[ $slot % count( $this->child_actions ) ]->execute();
                exit( 0 );
            }
        }

        while( -1 !== ( $pid_exit = pcntl_wait( $status ) ) )
        {
            if ( false !== ( $slot = array_search( $pid_exit, $this->children ) ) )
            {
                unset( $this->children[$slot] );
            }
        }
    }

    private function fork()
    {
        $pid = pcntl_fork();

        if( -1 == $pid )
        {
            throw new \RuntimeException( 'Unable to fork.' );
        }

        return $pid;
    }
}