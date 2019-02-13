<?php namespace Chukdo\Helper;

/**
 * Classe Convert
 * Fonctionnalités de converstion des données
 *
 * @package		helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Convert
{
    /**
     * Constructeur privé, empeche l'intanciation de la classe statique
     * @return void
     */
    private function __construct() {}

    /**
     * @param string $type
     * @param $value
     * @return array|bool|float|int|string
     */
    public static function toType(string $type, $value)
    {
        switch ($type) {
            case 'boolean' :
                return (bool) $value;
                break;
            case 'integer' :
                return (int) $value;
                break;
            case 'double' :
                return (float) $value;
                break;
            case 'array' :
                return (array) $value;
                break;
            case 'string' :
            default :
                return (string) $value;
        }
    }

    /**
     * @param string $name
     * @param string|null $prefix
     * @return string
     */
    public static function toQName(string $name, $prefix = null): string
    {
        $qname = str_replace(' ', '_', Data::allText($name));

        if (!preg_match('/^[a-z]/', $qname)) {
            $qname = $prefix ?: 'error';
        }

        return $qname;
    }

    /**
     * @param string $name
     * @param string|null $prefix
     * @param string|null $suffix
     * @return string
     */
    public static function toFileName(string $name, string $prefix = null, string $suffix = null): string
    {
        if (strlen($name) > 0) {
            return preg_replace(
                '/_{2,}/',
                '_',
                $prefix . str_replace(
                    ' ',
                    '_',
                    Data::allText($name)
                ).$suffix
            );
        }

        return '';
    }

    /**
     * @param string $value
     * @return string
     */
    public static function toUtf8(string $value): string
    {
        $value = (string) $value;

        if ($value !== false) {
            if (!mb_check_encoding($value, 'UTF-8')) {
                $value = mb_convert_encoding($value, 'UTF-8');
            }
        }
        return $value;
    }

    /**
     * @param $value
     * @return int
     */
    public static function toInt($value): int
    {
        return (int) self::toScalar($value);
    }

    /**
     * @param $value
     * @return float
     */
    public static function toFloat($value): float
    {
        $value = str_replace(' ', '', self::toScalar($value));

        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
        }

        return (float) str_replace(',', '.', $value);
    }

    /**
     * @param string $value
     * @param string|null $escape
     * @return string
     */
    public static function toStringJS(string $value, string $escape = null): string
    {
        $value = preg_replace(['/\n/', '/\r/'], ['\\n', ''], $value);

        if ($escape != '') {
            $value = str_replace($escape, '\\'.$escape, $value);
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string|null $format
     * @return \DateTime
     * @throws \Exception
     */
    public static function toDate(string $value, string $format = null): \DateTime
    {
        $date = \DateTime::createFromFormat($format ?: 'd/m/y', $value);

        if ($date instanceof \DateTime) {
            return $date;
        }

        return new \DateTime();
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function toScalar($value)
    {
        $scalar = '';

    	if (Test::isScalar($value)) {
        	$scalar = $value;

        } elseif (Test::isObject($value, '__toString')) {
        	$scalar = $value->__toString();

        } elseif (Test::isTraversable($value)) {
        	foreach($value as $v) {
        		$scalar .= self::toScalar($v).' ';
        	}
        } else {
        	$scalar = (string) $value;
        }

        return $scalar;
    }

    /**
     * @param $value
     * @return array
     */
    public static function toArray($value): array
    {
        $array = [];

        if (is_array($value)) {
        	$array = $value;

        /** La valeur est TRUE | FALSE | NULL | '' */
        } elseif ($value === true || $value === false || $value === null || $value === '' || $value === 0) {
            $array = [];

        /** La valeur est un entier ou une chaine de caractere */
        } elseif (Test::isScalar($value)) {
            $array = [$value];

        /** La valeur est un object avec une fonction de transformation */
        } elseif (Test::isObject($value, 'toArray')) {
            $array = $value->toArray();

        /** La valeur est un tableau ou est travsersable */
        } elseif (Test::isTraversable($value)) {
            foreach ($value as $k => $v) {
                $array[$k] = is_scalar($v) ? $v : self::toArray($v);
            }

        /** retourne un tableau vide */
        } else {
            $array = [];
        }

        return $array;
    }

    /**
     * @param $value
     * @return string
     */
    public static function toJson($value): string
    {
		if (is_scalar($value)) {
			return $value;

		} elseif (Test::isObject($value, 'toJson')) {
			return $value->toJson();

		} else {
    		return json_encode(self::toArray($value), JSON_PRETTY_PRINT);
		}
    }

    /**
     * @param $value
     * @return \Chukdo\Xml\Xml
     * @throws \Chukdo\Xml\NodeException
     * @throws \Chukdo\Xml\XmlException
     */
    public static function toXml($value): \Chukdo\Xml\Xml
    {
		if ($value instanceof \Chukdo\Xml\Xml) {
			return $value;

		} elseif (Test::isObject($value, 'toXml')) {
			return $value->toXml();

		} else {
			$xml = new \Chukdo\Xml\Xml();
			$xml->import($value);

			return $xml;
		}
    }
}