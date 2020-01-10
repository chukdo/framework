<?php

namespace Chukdo\Helper;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Json\Iterate;
use DateTime;
use stdClass;
use ArrayObject;
use Traversable;

/**
 * Classe Is
 * Fonctionnalités de test des données.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Is
{
    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function jsonString( $value = null ): bool
    {
        if ( !self::scalar( $value ) ) {
            return false;
        }
        if ( strpos( $value, '{' ) !== 0 ) {
            return false;
        }
        $json = json_decode( $value, true, 512, JSON_THROW_ON_ERROR );

        return $json && $value !== $json;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function scalar( $value ): bool
    {
        return is_scalar( $value );
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function null( $value ): bool
    {
        return $value === null;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function iterable( $value ): bool
    {
        return is_iterable( $value ) || self::arr( $value );
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function arr( $value ): bool
    {
        return is_array( $value ) || $value instanceof ArrayObject || $value instanceof stdClass || $value instanceof Iterate;
    }

    /**
     * @param             $value
     * @param string|null $method
     * @param string|null $property
     *
     * @return bool
     */
    public static function object( $value, string $method = null, string $property = null ): bool
    {
        if ( is_object( $value ) ) {
            if ( $method ) {
                return method_exists( $value, $method );
            }
            if ( $property ) {
                return property_exists( $value, $property );
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public static function qualifiedName( string $name ): bool
    {
        $letter     = " [^\d\W] ";
        $digit      = " \d ";
        $ncnamechar = " $letter | $digit | \. | - | _ ";
        $ncname     = " (?: $letter | _ )(?: $ncnamechar )* ";
        $qname      = " (?: $ncname: )? $ncname ";

        return preg_match( '/^' . $qname . '$/x', $name );
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     *
     * @return bool
     */
    public static function between( int $value, int $min = 0, int $max = 0 ): bool
    {
        $min = (int) $min;
        $max = (int) $max;
        if ( ( $min > 0 ) && $value < $min ) {
            return false;
        }
        if ( ( $max > 0 ) && $value > $max ) {
            return false;
        }

        return true;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function int( $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_INT ) !== false;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function float( $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_FLOAT ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function alpha( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[a-z]+$/iu', ], ] ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function alnum( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[a-z0-9]+$/iu', ], ] ) !== false;
    }

    /**
     * @param             $value
     * @param string|null $format
     *
     * @return bool
     */
    public static function date( $value, string $format = null ): bool
    {
        $format    = $format ?? 'd/m/Y';
        $checkDate = DateTime::createFromFormat( $format, $value );

        return $value === $checkDate->format( $format );
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function string( $value ): bool
    {
        return is_string( $value );
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function html( string $value ): bool
    {
        return strlen( strip_tags( $value ) ) !== strlen( $value );
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function url( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_URL ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function email( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_EMAIL ) !== false;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function zipcode( $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[0-9]{5}$/u', ], ] ) !== false;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function name( $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[a-zéèêëàäâùüûôöçîï\-\' ]+$/iu', ], ] ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function fileName( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[0-9a-z_\. ]+$/iu', ], ] ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function phone( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^(?:\+[1-9]|0)?\d{8,}$/iu', ], ] ) !== false;
    }

    /**
     * @param string $value
     *
     * @return bool
     */
    public static function mongoId( string $value ): bool
    {
        return filter_var( $value, FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/^[0-9abcdef]{22,26}$/iu', ], ] ) !== false;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function jsonInterface( $value ): bool
    {
        return $value instanceof JsonInterface;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function empty( $value ): bool
    {
        if ( self::scalar( $value ) ) {
            $value = trim( $value );

            if ( $value === '' || $value === null ) {
                return true;
            }
        }

        elseif ( self::traversable( $value ) ) {
            foreach ( $value as $v ) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function traversable( $value ): bool
    {
        return is_array( $value ) || $value instanceof Traversable;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public static function dateTime( $value ): bool
    {
        return $value instanceof DateTime;
    }
}
