<?php

namespace Chukdo\Helper;

/**
 * Classe To
 * Fonctionnalités de converstion des données.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class To
{
    /**
     * @param string $type
     * @param $value
     *
     * @return array|bool|float|int|string
     */
    public static function type( string $type, $value )
    {
        switch( $type ) {
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
     * @param string $name
     * @param string|null $prefix
     *
     * @return string
     */
    public static function qualifiedName( string $name, $prefix = null ): string
    {
        $qname = str_replace(
            ' ',
            '_',
            Str::allText($name)
        );

        if( !preg_match(
            '/^[a-z]/',
            $qname
        ) ) {
            $qname = $prefix
                ?: 'error';
        }

        return $qname;
    }

    /**
     * @param string $name
     * @param string|null $prefix
     * @param string|null $suffix
     *
     * @return string
     */
    public static function fileName( string $name, string $prefix = null, string $suffix = null ): string
    {
        if( strlen($name) > 0 ) {
            return preg_replace(
                '/_{2,}/',
                '_',
                $prefix . str_replace(
                    ' ',
                    '_',
                    Str::allText($name)
                ) . $suffix
            );
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

        if( $value !== false ) {
            if( !mb_check_encoding(
                $value,
                'UTF-8'
            ) ) {
                $value = mb_convert_encoding(
                    $value,
                    'UTF-8'
                );
            }
        }

        return $value;
    }

    /**
     * @param $value
     *
     * @return int
     */
    public static function int( $value ): int
    {
        return (int) self::scalar($value);
    }

    /**
     * @param $value
     *
     * @return float
     */
    public static function float( $value ): float
    {
        $value = str_replace(
            ' ',
            '',
            self::scalar($value)
        );

        if( strpos(
                $value,
                '.'
            ) !== false
            && strpos(
                $value,
                ','
            ) !== false ) {
            $value = str_replace(
                '.',
                '',
                $value
            );
        }

        return (float) str_replace(
            ',',
            '.',
            $value
        );
    }

    /**
     * @param string $value
     * @param string|null $format
     *
     * @return \DateTime
     *
     * @throws \Exception
     */
    public static function date( string $value, string $format = null ): \DateTime
    {
        $date = \DateTime::createFromFormat(
            $format
                ?: 'd/m/Y',
            $value
        );

        if( $date instanceof \DateTime ) {
            return $date;
        }

        return new \DateTime();
    }

    /**
     * @param $value
     *
     * @return mixed
     */
    public static function scalar( $value )
    {
        $scalar = '';

        if( Is::scalar($value) ) {
            $scalar = $value;
        } else if( Is::object(
            $value,
            '__toString'
        ) ) {
            $scalar = $value->__toString();
        } else if( Is::traversable($value) ) {
            foreach( $value as $v ) {
                $scalar .= self::scalar($v) . ' ';
            }
        } else {
            $scalar = (string) $value;
        }

        return $scalar;
    }

    /**
     * @param $value
     *
     * @return array
     */
    public static function arr( $value ): array
    {
        $array = [];

        if( is_array($value) ) {
            $array = $value;

            /* La valeur est TRUE | FALSE | NULL | '' */
        } else if( $value === true || $value === false || $value === null || $value === '' || $value === 0 ) {
            $array = [];

            /* La valeur est un entier ou une chaine de caractere */
        } else if( Is::scalar($value) ) {
            $array = [ $value ];

            /* La valeur est un object avec une fonction de transformation */
        } else if( Is::object(
            $value,
            'toArray'
        ) ) {
            $array = $value->toArray();

            /* La valeur est un tableau ou est travsersable */
        } else if( Is::traversable($value) ) {
            foreach( $value as $k => $v ) {
                $array[ $k ] = is_scalar($v)
                    ? $v
                    : self::arr($v);
            }

            /* retourne un tableau vide */
        } else {
            $array = [];
        }

        return $array;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public static function json( $value ): string
    {
        if( is_scalar($value) ) {
            return $value;
        } else if( Is::object(
            $value,
            'toJson'
        ) ) {
            return $value->toJson();
        } else {
            return json_encode(
                self::arr($value),
                JSON_PRETTY_PRINT
            );
        }
    }

    /**
     * @param $value
     *
     * @return \Chukdo\Xml\Xml
     *
     * @throws \Chukdo\Xml\NodeException
     * @throws \Chukdo\Xml\XmlException
     */
    public static function xml( $value ): \Chukdo\Xml\Xml
    {
        if( $value instanceof \Chukdo\Xml\Xml ) {
            return $value;
        } else if( Is::object(
            $value,
            'toXml'
        ) ) {
            return $value->toXml();
        } else {
            $xml = new \Chukdo\Xml\Xml();
            $xml->import($value);

            return $xml;
        }
    }
}
