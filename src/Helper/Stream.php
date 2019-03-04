<?php namespace Chukdo\Helper;

/**
 * Stream
 *
 * @package    Helper
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Stream
{
    /**
     * Stream constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param string $name
     * @param string $class
     */
    public static function register( string $name, string $class ): void
    {
        if ( self::exists( $name ) ) {
            stream_wrapper_unregister( $name );
        }
        stream_wrapper_register( $name, $class );
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function exists( string $name ): bool
    {
        return (bool) in_array( $name, stream_get_wrappers() );
    }
}