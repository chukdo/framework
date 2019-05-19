<?php

namespace Chukdo\Helper;

use Chukdo\Xml\Xml;
use DateTime;

/**
 * Classe To
 * Fonctionnalités de converstion des données.
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
     * @return string
     */
    public static function qualifiedName( string $name, $prefix = null ): string
    {
        $qname = str_replace(' ',
            '_',
            Str::allText($name));

        if ( !preg_match('/^[a-z]/',
            $qname) ) {
            $qname = $prefix
                ?: 'error';
        }

        return $qname;
    }

    /**
     * @param string      $name
     * @param string|null $prefix
     * @param string|null $suffix
     * @return string
     */
    public static function fileName( string $name, string $prefix = null, string $suffix = null ): string
    {
        if ( strlen($name) > 0 ) {
            return preg_replace('/_{2,}/',
                '_',
                $prefix . str_replace(' ',
                    '_',
                    Str::allText($name)) . $suffix);
        }

        return '';
    }

    /**
     * @param string $value
     * @return string
     */
    public static function utf8( string $value ): string
    {
        $value = (string) $value;

        if ( $value !== false ) {
            if ( !mb_check_encoding($value,
                'UTF-8') ) {
                $value = mb_convert_encoding($value,
                    'UTF-8');
            }
        }

        return $value;
    }

    /**
     * @param $value
     * @return int
     */
    public static function int( $value ): int
    {
        return (int) self::scalar($value);
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function scalar( $value )
    {
        $scalar = '';

        if ( Is::scalar($value) ) {
            $scalar = $value;
        }
        elseif ( Is::object($value, '__toString') ) {
            $scalar = $value->__toString();
        }
        elseif ( Is::traversable($value) ) {
            foreach ( $value as $v ) {
                $scalar .= self::scalar($v) . ' ';
            }
        }
        else {
            $scalar = (string) $value;
        }

        return $scalar;
    }

    /**
     * @param $value
     * @return float
     */
    public static function float( $value ): float
    {
        $value = str_replace(' ', '', self::scalar($value));

        if ( Str::contain($value, '.') && Str::contain($value, ',') ) {
            $value = str_replace('.', '', $value);
        }

        return (float) str_replace(',', '.', $value);
    }

    /**
     * @param string      $value
     * @param string|null $format
     * @return DateTime
     * @throws \Exception
     */
    public static function date( string $value, string $format = null ): DateTime
    {
        $date = DateTime::createFromFormat($format
            ?: 'd/m/Y',
            $value);

        if ( $date instanceof DateTime ) {
            return $date;
        }

        return new DateTime();
    }

    /**
     * @param $value
     * @return string
     */
    public static function json( $value ): string
    {
        if ( is_scalar($value) ) {
            return $value;
        }
        elseif ( Is::object($value, 'toJson') ) {
            return $value->toJson();
        }
        else {
            return json_encode(self::arr($value), JSON_PRETTY_PRINT);
        }
    }

    /**
     * @param $value
     * @return array
     */
    public static function arr( $value ): array
    {
        $array = [];

        if ( is_array($value) ) {
            $array = $value;

            /* La valeur est TRUE | FALSE | NULL | '' */
        }
        elseif ( $value === true || $value === false || $value === null || $value === '' || $value === 0 ) {
            $array = [];

            /* La valeur est un entier ou une chaine de caractere */
        }
        elseif ( Is::scalar($value) ) {
            $array = [ $value ];

            /* La valeur est un object avec une fonction de transformation */
        }
        elseif ( Is::object($value,
            'toArray') ) {
            $array = $value->toArray();

            /* La valeur est un tableau ou est travsersable */
        }
        elseif ( Is::traversable($value) ) {
            foreach ( $value as $k => $v ) {
                $array[ $k ] = is_scalar($v)
                    ? $v
                    : self::arr($v);
            }

            /* retourne un tableau vide */
        }
        else {
            $array = [];
        }

        return $array;
    }

    /**
     * @param $value
     * @return Xml
     */
    public static function xml( $value ): Xml
    {
        if ( $value instanceof Xml ) {
            return $value;
        }
        elseif ( Is::object($value, 'toXml') ) {
            return $value->toXml();
        }
        else {
            $xml = new Xml();
            $xml->import($value);

            return $xml;
        }
    }

    /**
     * @param             $value
     * @param string|null $title
     * @param string|null $color
     * @param bool        $type
     * @return string
     */
    public static function html( $value, string $title = null, string $color = null, bool $type = false ): string
    {
        $html  = '';
        $style = 'border-spacing:0;border-collapse:collapse;font-family:Arial;width:100%;word-break:break-word;';
        $title = $title
            ?: ( $type
                ? Str::type($value)
                : null );

        if ( $title ) {
            $color = $color
                ?: '#499cef';
            $html  .= '<thead style="color: #fff;background: ' . $color
                      . ';"><tr><th colspan="2" style="padding:10px;font-size:20px;">' . ucfirst($title)
                      . "</th></tr></thead>";
        }

        foreach ( $value as $k => $v ) {
            if ( is_iterable($v) ) {
                $v = self::html($v, $type
                    ? Str::type($v)
                    : null, null, $type);
            }
            elseif ( $v instanceof \DateTime ) {
                $v = $v->format('d-m-Y H:i:s');
            }

            $html .= '<tr><td style="background:#eee;padding:6px;border:1px solid #eee;width:' . strlen($k) * 9 . 'px;">'
                     . $k . '</td><td  style="padding:6px;border:1px solid #eee;">' . $v . '</td></tr>';
        }

        return '<table id="ToHtml" style="' . $style . '">' . $html . '</table>';
    }
}
