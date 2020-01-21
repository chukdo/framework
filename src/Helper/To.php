<?php

namespace Chukdo\Helper;

use Chukdo\Xml\Xml;
use DateTime;
use Exception;
use function is_null;
use function is_string;

/**
 * Classe To
 * Fonctionnalités de converstion des données.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class To
{
    /**
     * @param string $type
     * @param        $value
     *
     * @return array|bool|float|int|string
     */
    public static function type( string $type, $value )
    {
        switch ( $type ) {
            case 'boolean':
                return (bool) $value;
                break;
            case 'integer':
                return (int) $value;
                break;
            case 'double':
                return (float) $value;
                break;
            case 'array':
                return (array) $value;
                break;
            case 'string':
            default:
                return (string) $value;
        }
    }

    /**
     * @param string      $name
     * @param string|null $prefix
     *
     * @return string
     */
    public static function qualifiedName( string $name, $prefix = null ): string
    {
        $qname = str_replace( ' ', '_', Str::allText( $name ) );
        if ( !preg_match( '/^[a-z]/', $qname ) ) {
            $qname = $prefix ?? 'error';
        }

        return $qname;
    }

    /**
     * @param string      $name
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return string
     */
    public static function fileName( string $name, string $prefix = null, string $suffix = null ): string
    {
        if ( $name !== '' ) {
            return preg_replace( '/_{2,}/', '_', $prefix . str_replace( ' ', '_', Str::allText( $name ) ) . $suffix );
        }

        return '';
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function utf8( string $value ): string
    {
        $value = (string) $value;

        if ( ( $value !== false ) && !mb_check_encoding( $value, 'UTF-8' ) ) {
            $value = mb_convert_encoding( $value, 'UTF-8' );
        }

        return $value;
    }

    /**
     * @param string $value
     *
     * @return string|null
     */
    public static function base64Decode( string $value ): ?string
    {
        return base64_decode( $value ) ?? null;
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function base64UrlEncode( string $value ): string
    {
        return str_replace( [
                                '+',
                                '/',
                                '=',
                            ], [
                                '-',
                                '_',
                                '',
                            ], self::base64Encode( $value ) );
    }

    /**
     * @param string $value
     *
     * @return string
     */
    public static function base64Encode( string $value ): string
    {
        return base64_encode( $value );
    }

    /**
     * @param $value
     *
     * @return int
     */
    public static function int( $value ): int
    {
        return (int) self::scalar( $value );
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function scalar( $value )
    {
        $scalar = '';
        if ( Is::scalar( $value ) ) {
            $scalar = $value;
        }
        elseif ( Is::object( $value, '__toString' ) ) {
            $scalar = $value->__toString();
        }
        elseif ( Is::traversable( $value ) ) {
            foreach ( $value as $v ) {
                $scalar .= self::scalar( $v ) . ' ';
            }
        }
        else {
            $scalar = (string) $value;
        }

        return $scalar;
    }

    /**
     * @param $value
     *
     * @return float
     */
    public static function float( $value ): float
    {
        $value = str_replace( ' ', '', self::scalar( $value ) );

        if ( Str::contain( $value, '.' ) && Str::contain( $value, ',' ) ) {
            $value = str_replace( '.', '', $value );
        }

        return (float) str_replace( ',', '.', $value );
    }

    /**
     * @param string      $value
     * @param string|null $format
     *
     * @return DateTime
     * @throws Exception
     */
    public static function date( string $value, string $format = null ): DateTime
    {
        $date = DateTime::createFromFormat( $format ?? 'd/m/Y', $value );

        if ( $date instanceof DateTime ) {
            return $date;
        }

        return new DateTime();
    }

    /**
     * @param $value
     *
     * @return String
     */
    public static function xml( $value ): String
    {
        if ( is_scalar( $value ) && strpos( $value, '<' ) === 0 ) {
            return $value;
        }

        if ( Is::object( $value, 'toXmlString' ) ) {
            return $value->toXmlString();
        }

        $xml = new Xml();
        $xml->import( $value );

        return $xml->toXmlString();
    }

    /**
     * @param     $value
     * @param int $indent
     *
     * @return string
     */
    public static function text( $value, int $indent = 0 ): string
    {
        $text   = '';
        $prefix = str_repeat( ' |  ', $indent );
        if ( is_numeric( $value ) ) {
            $text .= "Number: $value";
        }
        else {
            if ( is_string( $value ) ) {
                $text .= "String: '$value'";
            }
            else {
                if ( $value === null ) {
                    $text .= 'Null';
                }
                else {
                    if ( $value === true ) {
                        $text .= 'True';
                    }
                    else {
                        if ( $value === false ) {
                            $text .= 'False';
                        }
                        else {
                            if ( is_array( $value ) ) {
                                $text .= 'Array (' . count( $value ) . ')';
                                $indent++;
                                foreach ( $value AS $k => $v ) {
                                    $text .= "\n$prefix [$k] = ";
                                    $text .= self::text( $v, $indent );
                                }
                            }
                            else {
                                if ( is_object( $value ) ) {
                                    $text .= 'Object (' . get_class( $value ) . ')';
                                    $indent++;
                                    foreach ( $value AS $k => $v ) {
                                        $text .= "\n$prefix $k -> ";
                                        $text .= self::text( $v, $indent );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $text;
    }

    /**
     * @param             $value
     * @param string|null $title
     * @param string|null $color
     * @param bool        $type
     *
     * @return string
     */
    public static function html( $value, string $title = null, string $color = null, bool $type = false ): string
    {
        $html  = '';
        $title ??= $type
            ? Str::type( $value )
            : null;

        if ( $title ) {
            $color = $color
                ?: '#333';
        }

        $style      = 'style="border-spacing:0;border-collapse:collapse;font-family:Arial;font-size:12px;width:100%;word-break:break-word;border-radius:3px;overflow:hidden;"';
        $styleTHEAD = 'style="background: #ddd;color: ' . $color . ';"';
        $styleTH    = 'style="padding:8px;font-size:14px;font-weight: normal;"';
        $styleTR    = 'style="font-size:12px;font-weight: normal;"';
        $styleTDKey = 'style="background:#eee;padding:8px;border:1px solid #ddd;"';
        $styleTDVal = 'style="padding:8px;border:1px solid #ddd;"';

        if ( Is::scalar( $value ) ) {
            return '<span ' . $style . '><b>(' . gettype( $value ) . ')</b>: ' . $value . '<span>';
        }

        if ( Is::dateTime( $value ) ) {
            return '<span ' . $style . '><b>DateTime</b>: ' . $value->format( 'd-m-Y H:i:s' ) . '<span>';
        }

        if ( Is::jsonInterface( $value ) ) {
            return '<pre ' . $style . '>' . Str::replace( [
                                                              '/:(.*?)([,|\r|\n])/',
                                                              '/([\r|\n])(.*?):/',
                                                          ], [
                                                              ': <span style="color:#ef6500;">$1</span>$2',
                                                              '$1<span style="color:#75c100;">$2</span> :',
                                                          ], self::json( $value ) ) . '</pre>';
        }

        if ( Is::xml( $value ) ) {
            return '<pre ' . $style . '>' . Str::replace( [
                                                              '/<(.*?)>/',
                                                          ], [
                                                              '<span style="color:#ef6500;">&lt;$1&gt;</span>',
                                                          ], self::xml( $value ) ) . '</pre>';
        }

        if ( $title ) {
            $color = $color
                ?: '#333';
            $html  .= '<thead ' . $styleTHEAD . '><tr><th colspan="2" ' . $styleTH . '>' . ucfirst( $title ) . '</th></tr></thead>';
        }

        if ( Is::iterable( $value ) ) {
            foreach ( $value as $k => $v ) {
                $v    = self::html( $v, null, null, $type );
                $html .= '<tr ' . $styleTR . '><td ' . $styleTDKey . '>' . $k . '</td><td ' . $styleTDVal . '>' . $v . '</td></tr>';
            }
        }
        else {
            $html .= '<tr ' . $styleTR . '><td ' . $styleTDKey . '>Dump</td><td ' . $styleTDVal . '>' . print_r( $value, true ) . '</td></tr>';
        }

        return '<table id="ToHtml" style="' . $style . '">' . $html . '</table>';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function json( $value ): string
    {
        if ( is_scalar( $value ) ) {
            return $value;
        }

        if ( Is::object( $value, 'toJson' ) ) {
            return $value->toJson();
        }

        return json_encode( self::arr( $value ), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT, 512 );
    }

    /**
     * @param $value
     *
     * @return array
     */
    public static function arr( $value ): array
    {
        $array = [];
        if ( is_array( $value ) ) {
            $array = $value;
        }

        /** La valeur est TRUE | FALSE | NULL | '' */
        elseif ( $value === true || $value === false || $value === null || $value === '' || $value === 0 ) {
            $array = [];
        }

        /** La valeur est un entier ou une chaine de caractere */
        elseif ( Is::scalar( $value ) ) {
            $array = [ $value ];
        }

        /** La valeur est un object avec une fonction de transformation */
        elseif ( Is::object( $value, 'toArray' ) ) {
            $array = $value->toArray();
        }

        /** La valeur est un tableau ou est travsersable */
        elseif ( Is::traversable( $value ) ) {
            foreach ( $value as $k => $v ) {
                $array[ $k ] = is_scalar( $v )
                    ? $v
                    : self::arr( $v );
            }
        }

        /** retourne un tableau vide */
        else {
            $array = [];
        }

        return $array;
    }
}
