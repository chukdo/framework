<?php namespace Chukdo\Helper;

/**
 * Classe Test
 * Fonctionnalités de test des données
 *
 * @package     helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Test
{
    /**
     * @param $value
     * @return bool
     */
    public static function isEmpty($value): bool
    {
        if (self::isScalar($value)) {
            $value = trim($value);

            if ($value === '' || $value === null) {
                return true;
            }
        } elseif (self::isTraversable($value)) {
            foreach ($value as $v) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isScalar($value): bool
    {
        return is_scalar($value);
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isJson($value): bool
    {
        $json = json_decode($value);
        return $json && $value != $json;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isArray($value): bool
    {
        return is_array($value) || $value instanceof \ArrayObject;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isTraversable($value): bool
    {
        return is_array($value) || $value instanceof \Traversable;
    }

    /**
     * @param $value
     * @param string $method
     * @param string $property
     * @return bool
     */
    public static function isObject($value, string $method = '', string $property = ''): bool
    {
        if (is_object($value)) {
            if ($method != false) {
                return method_exists($value, $method);
            } else if ($property != false) {
                return property_exists($value, $property);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public static function isQName(string $name): bool
    {
        $letter     = " [^\d\W] ";
        $digit      = " \d ";
        $ncnamechar = " $letter | $digit | \. | - | _ ";
        $ncname     = " (?: $letter | _ )(?: $ncnamechar )* ";
        $qname      = " (?: $ncname: )? $ncname ";

        return preg_match('/^'.$qname.'$/x', $name);
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function isBetween(int $value, int $min = 0, int $max = 0): bool
    {
        $min = (int) $min;
        $max = (int) $max;

        if ($min > 0) {
            if ($value < $min) {
                return false;
            }
        }

        if ($max > 0) {
            if ($value > $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isInt($value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT
        ) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isFloat($value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_FLOAT
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isAlpha(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
                [
                    'options' => [
                        'regexp' => '/^[a-z]+$/iu'
                    ]
                ]
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isAlnum(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^[a-z0-9]+$/iu'
                ]
            ]
        ) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isString($value): bool
    {
        return is_string($value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function ishtml(string $value): bool
    {
        return strlen(strip_tags($value)) != strlen($value);
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isUrl(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_URL
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isMail(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_EMAIL
        ) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isZipcode($value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^[0-9]{5}$/u'
                ]
            ]
        ) !== false;
    }

    /**
     * @param $value
     * @return bool
     */
    public static function isName($value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^[a-zéèêëàäâùüûôöçîï\-\' ]+$/iu'
                ]
            ]
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isFileName(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^[0-9a-z_\. ]+$/iu'
                ]
            ]
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isPhone(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^(?:\+[1-9]|0)?\d{8,}$/iu'
                ]
            ]
        ) !== false;
    }

    /**
     * @param string $value
     * @return bool
     */
    public static function isMongoId(string $value): bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_REGEXP,
            [
                'options' => [
                    'regexp' => '/^[0-9abcdef]{22,26}$/iu'
                ]
            ]
        ) !== false;
    }
}