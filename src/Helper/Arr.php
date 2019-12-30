<?php

namespace Chukdo\Helper;

use Chukdo\Json\Iterate;
use Closure;

/**
 * Classe Iterate
 * Fonctionnalités des tableaux.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Arr
{
    /**
     * @param array $array
     *
     * @return int
     */
    public static function count( array $array ): int
    {
        return count( $array );
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function empty( array $array ): bool
    {
        return empty( $array );
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public static function spreadArgs( array $args ): array
    {
        if ( isset( $args[ 0 ] ) && self::isArray( $args[ 0 ] ) ) {
            return $args[ 0 ];
        }

        return $args;
    }

    /**
     * @param $array
     *
     * @return bool
     */
    public static function isArray( $array ): bool
    {
        return is_array( $array );
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function hasContent( array $array ): bool
    {
        return !empty( $array );
    }

    /**
     * @param       $value
     * @param array $array
     * @param bool  $unique
     *
     * @return array
     */
    public static function append( $value, array $array, bool $unique = true ): array
    {
        if ( $unique === false || ( $unique === true && !self::in( $value, $array ) ) ) {
            $array[] = $value;
        }

        return $array;
    }

    /**
     * @param       $value
     * @param array $array
     *
     * @return bool
     */
    public static function in( $value, array $array ): bool
    {
        return in_array( $value, $array, true );
    }

    /**
     * @param array $pushTo
     * @param array $toPush
     *
     * @param bool  $unique
     *
     * @return array
     */
    public static function push( array $pushTo, array $toPush, bool $unique = true ): array
    {
        foreach ( $toPush as $push ) {
            if ( $unique === false || ( $unique === true && !self::in( $push, $pushTo ) ) ) {
                $pushTo[] = $push;
            }
        }

        return $pushTo;
    }

    /**
     * @param array   $array
     * @param Closure $callback
     *
     * @return array
     */
    public static function filter( array $array, Closure $callback ): array
    {
        return array_filter( $array, $callback );
    }

    /**
     * @param array   $array
     * @param Closure $callback
     *
     * @return array
     */
    public static function filterRecursive( array $array, Closure $callback ): array
    {
        foreach ( $array as &$value ) {
            if ( Is::arr( $value ) ) {
                $value = self::filterRecursive( $value, $callback );
            }
            else {
                $value = $callback( $value );
            }
        }

        return $array;
    }

    /**
     * @param array       $array
     * @param string|null $path
     * @param             $value
     *
     * @return array
     */
    public static function inc( array &$array, ?string $path, int $value ): array
    {
        return self::set( $array, $path, $value + self::get( $array, $path ) );
    }

    /**
     * @param array       $array
     * @param string|null $path
     * @param             $value
     *
     * @return array
     */
    public static function set( array &$array, ?string $path, $value ): array
    {
        if ( $path === null ) {
            $array[] = $value;

            return $array;
        }

        if ( Str::notContain( $path, '.' ) ) {
            $array[ $path ] = $value;

            return $array;
        }

        $arr       = new Iterate( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join( '.' );

        if ( !isset( $array[ $firstPath ] ) || !Is::arr( $array[ $firstPath ] ) ) {
            $array[ $firstPath ] = [];
        }
        self::set( $array[ $firstPath ], $endPath, $value );

        return $array;
    }

    /**
     * @param array       $array
     * @param string|null $path
     *
     * @return mixed|null
     */
    public static function get( array &$array, ?string $path )
    {
        if ( $path === null ) {
            return null;
        }

        if ( Str::notContain( $path, '.' ) ) {
            return $array[ $path ] ?? null;
        }

        $arr       = new Iterate( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join( '.' );
        $get       = $array[ $firstPath ] ?? null;

        if ( Is::arr( $get ) ) {
            return self::get( $get, $endPath );
        }

        return null;
    }

    /**
     * @param array       $array
     * @param string|null $path
     * @param             $value
     *
     * @return array
     */
    public static function addToSet( array &$array, ?string $path, $value ): array
    {
        $get = self::get( $array, $path );

        if ( $get === null ) {
            self::set( $array, $path, [ $value ] );
        }
        else {
            if ( Is::arr( $get ) ) {
                $get[] = $value;
                self::set( $array, $path, $get );
            }
            else {
                self::set( $array, $path, [
                    $get,
                    $value,
                ] );
            }
        }

        return $array;
    }

    /**
     * @param array       $array
     * @param string|null $path
     *
     * @return mixed|null
     */
    public static function unset( array &$array, ?string $path )
    {
        if ( $path === null || !isset( $array[ $path ] ) ) {
            return null;
        }

        if ( Str::notContain( $path, '.' ) ) {
            $get = $array[ $path ];
            unset( $array[ $path ] );

            return $get;
        }

        $arr       = new Iterate( Str::split( $path, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join( '.' );
        $get       = $array[ $firstPath ] ?? null;

        if ( Is::arr( $get ) ) {
            return self::unset( $get, $endPath );
        }

        return null;
    }

    public static function unwind( array &$array, ?string $path ): array
    {
        $unwinded = [];
        $toUnwind = self::get( $array, $path );

        if ( self::isArray( $toUnwind ) ) {
            foreach ( $toUnwind as $unwind ) {
                $cloneArray = self::merge( [], $array );
                $unwinded[] = self::set( $cloneArray, $path, $unwind );
            }
        }

        return $unwinded;
    }

    /**
     * @param array $mergeTo
     * @param array $toMerge
     *
     * @return array
     */
    public static function merge( array $mergeTo, array $toMerge ): array
    {
        foreach ( $toMerge as $key => $merge ) {
            $mergeTo[ $key ] = $merge;
        }

        return $mergeTo;
    }
}
