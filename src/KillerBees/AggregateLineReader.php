<?php

namespace KillerBees;

class AggregateLineReader
{
	private static $sub_streams = array();
	private static $is_registered = false;
	private $pointer = 0;
	private $unique_id;
	private $line_remainder = '';

	const PROTOCOL_WRAPPER = 'aggregateread';

	public static function register()
	{
		if ( !self::$is_registered )
		{
			stream_wrapper_register( static::PROTOCOL_WRAPPER, __CLASS__ );
			self::$is_registered = true;
		}
	}

	public static function unregister()
	{
		if ( self::$is_registered )
		{
			stream_wrapper_unregister( static::PROTOCOL_WRAPPER );
			self::$is_registered = false;
		}
	}

	public static function addStream( $file_name, $sub_stream )
	{
		$protocol   = static::PROTOCOL_WRAPPER .  '://';
		$file_name  = preg_replace( '/^' . preg_quote( $protocol, '/' ) . '/', '', $file_name );

		@fseek( $sub_stream, 0 ); // Stream might not support seeking.

		if ( !isset( self::$sub_streams[$file_name] ) )
		{
			self::$sub_streams[$file_name] = array();
		}

		self::$sub_streams[$file_name][] = $sub_stream;

		reset( self::$sub_streams[$file_name] );
	}

	public function stream_open( $path, $mode, $options, &$opened_path )
	{
		$protocol = static::PROTOCOL_WRAPPER .  '://';
		if ( $protocol !== substr( $path, 0, strlen( $protocol ) ) )
		{
			trigger_error( "Please use $protocol for " . get_class( $this ), E_USER_WARNING );
			return false;
		}
		if ( "r" !== $mode )
		{
			trigger_error( "Only 'r' mode is supported in " . get_class( $this ), E_USER_WARNING );
			return false;
		}

		$this->unique_id = substr( $path, strlen( $protocol ) );
		self::$sub_streams[$this->unique_id] = array();
		$this->pointer = 0;

		return true;
	}

	public function stream_read( $count )
	{
		while (
			( next( self::$sub_streams[$this->unique_id] )
					|| reset( self::$sub_streams[$this->unique_id] )
			)
		)
		{
			if ( $this->stream_eof() )
			{
				break;
			}

			$current_stream = current( self::$sub_streams[$this->unique_id] );

			if ( feof( $current_stream ) )
			{
				continue;
			}

			$buffer = fgets( $current_stream, $count );

			if ( false === $buffer )
			{
				continue;
			}

			return $buffer;
		}

		return false;
	}

	public function stream_seek( $offset, $whence = SEEK_SET )
	{
		trigger_error( "Seek is not supported", E_USER_WARNING );
		return -1;
	}

	public function stream_eof()
	{
		return array_reduce( array_map( function( $stream )
		{
			return feof( $stream );
		}, self::$sub_streams[$this->unique_id] ), function ( $feof_1, $feof_2 )
		{
			return $feof_1 && $feof_2;
		}, true );
	}

	public function stream_tell()
	{
		return $this->pointer;
	}

	public function stream_stat()
	{
		return array();
	}

	public function stream_close()
	{
	}
}
