<?php

namespace Chukdo\Helper;

use Chukdo\Json\Json;

/**
 * Classe Str
 * Fonctionnalités des chaines de caracteres.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Str
{
    /**
     * @param string $name
     *
     * @return string
     */
    public static function extension( string $name ): string
    {
        $name = strtolower( $name );
        $pos  = strrpos( $name, '.' );
        if ( $pos !== false ) {
            return substr( $name, strrpos( $name, '.' ) + 1 );
        }

        return $name;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function type( $value ): string
    {
        $type = gettype( $value );
        if ( $type === 'object' ) {
            return get_class( $value );
        }

        return $type;
    }

    /**
     * Verifie si une chaine de caractere ne contient pas une autre chaine de caractere.
     *
     * @param string $haystack La chaîne dans laquelle on doit chercher
     * @param string $needle   valeur recherché
     *
     * @return bool
     */
    public static function notContain( string $haystack, string $needle ): bool
    {
        return !self::contain( $haystack, $needle );
    }

    /**
     * Verifie si une chaine de caractere contient une autre chaine de caractere.
     *
     * @param string $haystack La chaîne dans laquelle on doit chercher
     * @param string $needle   valeur recherché
     *
     * @return bool
     */
    public static function contain( string $haystack, string $needle ): bool
    {
        return strpos( $haystack, $needle ) !== false;
    }

    /**
     * @param string $haystack La chaîne dans laquelle on doit chercher
     * @param string $needle   le debut de chaine à verifier
     *
     * @return bool
     */
    public static function startWith( string $haystack, string $needle ): bool
    {
        return strpos( $haystack, $needle ) === 0;
    }

    /**
     * @param string $haystack La chaîne dans laquelle on doit chercher
     * @param string $needle   la fin de chaine à verifier
     *
     * @return bool
     */
    public static function endWith( string $haystack, string $needle ): bool
    {
        return substr( $haystack, strlen( $needle ) ) === $needle;
    }

    /**
     * @param string   $delimiter
     * @param string   $string
     * @param int|null $length
     *
     * @return array
     */
    public static function explode( string $delimiter, string $string, int $length = null ): array
    {
        $explode = explode( $delimiter, $string );

        if ( $explode === false ) {
            $explode = [];
        }

        if ( count( $explode ) === 1 && $explode[ 0 ] === '' ) {
            $explode = [];
        }

        if ( $length ) {
            return array_pad( $explode, $length, null );
        }

        return $explode;
    }

    /**
     * @param string $string
     * @param int    $index
     *
     * @return string|null
     */
    public static function charAt( string $string, int $index ): ?string
    {
        if ( $index < strlen( $string ) ) {
            return $string[ $index ];
        }

        return null;
    }

    /**
     * @param string $pattern
     * @param string $value
     *
     * @return Json
     */
    public static function matchAll( string $pattern, string $value ): Json
    {
        $match   = new Json();
        $matches = [];

        preg_match_all( $pattern, $value, $matches, PREG_SET_ORDER );

        foreach ( $matches as $k => $array ) {
            switch ( count( $array ) ) {
                case 0:
                    break;
                case 1:
                    $match->append( $array[ 0 ] );
                    break;
                case 2:
                    $match->append( $array[ 1 ] );
                    break;
                default:
                    array_shift( $array );
                    $match->append( $array );
            }
        }

        return $match;
    }

    /**
     * @param string $pattern
     * @param string $value
     *
     * @return string|null
     */
    public static function matchOne( string $pattern, string $value ): ?string
    {
        $match = [];

        preg_match( $pattern, $value, $match );

        switch ( count( $match ) ) {
            case 1:
                return $match[ 0 ];
            case 2:
                return $match[ 1 ];
            default:
                return null;
        }
    }

    /**
     * @param string $pattern
     * @param string $value
     *
     * @return Json|string|null
     */
    public static function match( string $pattern, string $value )
    {
        $match = [];

        preg_match( $pattern, $value, $match );

        switch ( count( $match ) ) {
            case 0:
                return null;
            case 1:
                return $match[ 0 ];
            case 2:
                return $match[ 1 ];
            default:
                array_shift( $match );

                return new Json( $match );
        }
    }

    /**
     * @param string   $value
     * @param string   $delimiter
     * @param int|null $pad
     * @param null     $padValue
     *
     * @return array
     */
    public static function split( string $value, string $delimiter, int $pad = null, $padValue = null ): array
    {
        $split = self::explode( $delimiter, $value );

        if ( $pad ) {
            $split = array_pad( $split, $pad, $padValue );
        }

        return $split;
    }

    /**
     * @param array  $value
     * @param string $glue
     *
     * @return string
     */
    public static function join( array $value, string $glue ): string
    {
        return implode( $glue, $value );
    }

    /**
     * @param int|null $time
     *
     * @return string
     */
    public static function time( int $time = null ): string
    {
        if ( $time < 0.1 ) {
            return round( $time * 1000, 3 ) . ' Micro-secondes';
        }
        if ( $time < 1 ) {
            return round( $time * 1000, 3 ) . ' Milli-secondes';
        }
        if ( $time ) {
            return round( $time, 3 ) . ' Secondes';
        }

        return '0';
    }

    /**
     * @param int $mem
     *
     * @return string
     */
    public static function memory( int $mem = null ): string
    {
        if ( $mem < 1024 ) {
            return $mem . ' Octets';
        }
        if ( $mem < 1048576 ) {
            return round( $mem / 1024, 2 ) . ' Kilo-octets';
        }
        if ( $mem ) {
            return round( $mem / 1048576, 2 ) . ' Mega-octets';
        }

        return '0';
    }

    /**
     * @param string|null $prefix
     *
     * @return string
     */
    public static function uid( string $prefix = null ): string
    {
        return $prefix . md5( uniqid( (string) mt_rand(), true ) );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function stripSpaceBetweenTag( string $value ): string
    {
        return self::trim( self::replace( '/>[\s|\t|\r|\n]+</', '><', $value ) );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function trim( string $value ): string
    {
        $value = self::replace( '/\n|\r|\t/', ' ', $value );
        $value = self::replace( '/\s{2,}/', ' ', $value );

        return trim( $value );
    }

    /**
     * @param        $pattern
     * @param        $replacement
     * @param string $value
     *
     * @return string
     */
    public static function replace( $pattern, $replacement, string $value ): string
    {
        return (string) preg_replace( $pattern, $replacement, $value );
    }

    /**
     * @param string $value
     * @param int    $len
     *
     * @return string
     */
    public static function ellipsis( string $value, int $len ): string
    {
        if ( strlen( $value ) > $len ) {
            return substr( $value, 0, $len ) . '...';
        }

        return $value;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function allDigit( string $value ): string
    {
        return self::replace( '/[^\d]/u', '', $value );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function allText( string $value ): string
    {
        $text = strtolower( trim( self::replace( '/[^[:alnum:]]/u', ' ', self::removeSpecialChars( self::stripTag( $value ) ) ) ) );

        return $text;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function removeSpecialChars( string $value ): string
    {
        return self::replace( [
                                  '/[éèêë]/iu',
                                  '/[àäâ]/iu',
                                  '/[ùüû]/iu',
                                  '/[ôö]/iu',
                                  '/[ç]/iu',
                                  '/[îï]/iu',
                                  '/²/iu',
                                  '/°/iu',
                                  '/œ/iu',
                              ], [
                                  'e',
                                  'a',
                                  'u',
                                  'o',
                                  'c',
                                  'i',
                                  '2',
                                  '.',
                                  'oe',
                              ], self::trim( $value ) );
    }

    /**
     * @param string $value
     * @param string $tag
     * @param string $replacement
     *
     * @return string
     */
    public static function stripTag( string $value, string $tag = null, string $replacement = null ): string
    {
        return self::replace( '/<\/?\s*' . $tag . '[^>]*>/', $replacement ?? ' ', $value );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function allSentence( string $value ): string
    {
        $text = strtolower( trim( self::replace( '/[^[:alnum:]_;:\., ]/u', ' ', self::removeSpecialChars( self::stripTag( $value ) ) ) ) );

        return $text;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function removeWhiteSpace( string $value ): string
    {
        return self::replace( '/ /', '', $value );
    }
}
