<?php

namespace KillerBees\Process;

class ForkerTest extends \PHPUnit_Framework_TestCase
{
    static private $pid_file;

    public function setUp()
    {
        self::$pid_file = sys_get_temp_dir() . '/' . uniqid();
    }

    public function tearDown()
    {
        if ( is_file( self::$pid_file ) )
        {
            unlink( self::$pid_file );
        }
    }

    public function testForking()
    {
        $action = $this->getMockForAbstractClass( 'KillerBees\Process\Action', array( 'execute' ) );
        $action->expects( $this->any() )
            ->method( 'execute' )
            ->will( $this->returnCallback( array( $this, 'writeMyPid' ) ) );

        $forker = new Forker( 2 );

        $forker->registerChildAction( $action );
        $forker->registerChildAction( $action );

        $forker->start();

        $pid_file_contents  = file_get_contents( self::$pid_file );
        $children_pids      = preg_split( "/\n/", $pid_file_contents, -1, PREG_SPLIT_NO_EMPTY );
        $parent_pid         = getmypid();

        $this->assertEquals( 2, count( $children_pids ), 
            "PID list should contain two items" );
        $this->assertNotEquals( $children_pids[0], $children_pids[1], 
            "Children processes should have different PIDs" );
        $this->assertNotEquals( $parent_pid, $children_pids[0], 
            "Child 1 should have different PID from parent" );
        $this->assertNotEquals( $parent_pid, $children_pids[1],
            "Child 2 should have different PID from parent" );
    }

    public static function writeMyPid()
    {
        file_put_contents( self::$pid_file, getmypid() . "\n", FILE_APPEND );
        exit( 0 );
    }
}