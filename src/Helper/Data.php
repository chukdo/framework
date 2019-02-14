<?php namespace Chukdo\Helper;

Use \Chukdo\Json\Json;

/**
 * Classe Data
 * Fonctionnalités de filtre sur les données
 *
 * @package		helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class data
{
    /**
     * Constructeur privé, empeche l'intanciation de la classe statique
     * @return void
     */
    private function __construct() {}

    /**
     * @param string $name
     * @return string
     */
    public static function extension(string $name): string
    {
        $name   = strtolower($name);
        $pos    = strrpos($name, '.');

        if ($pos !== false) {
            return substr($name, strrpos($name, '.') + 1);
        }

        return $name;
    }

    /**
     * Verifie si une chaine de caractere contient une autre chaine de caractere
     *
     * @param 	string  $haystack La chaîne dans laquelle on doit chercher
     * @param 	string	$needle valeur recherché
     * @return 	bool
     */
    public static function contain(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === false ? false : true;
    }

    /**
     * Verifie si une chaine de caractere ne contient pas une autre chaine de caractere
     *
     * @param 	string 	$haystack La chaîne dans laquelle on doit chercher
     * @param 	string	$needle valeur recherché
     * @return 	bool
     */
    public static function notContain(string $haystack, string $needle): bool
    {
        return !self::contain($haystack, $needle);
    }

    /**
     * Retourne un caractere d'une chaine en fonction de sa position
     *
     * @param 	string 	$string
     * @param 	int 	$index
     * @return 	string
     */
    public static function charAt(string $string, int $index): string
    {
        if ($index < strlen($string)) {
            return substr($string, $index, 1);
        } else{
            return -1;
        }
    }

    /**
     * @param int|null $duration
     * @return string
     */
    public static function csrfTokenEncode(int $duration = null): string
    {
        return self::encrypt(json_encode([
            'time'      => time(),
            'duration'  => (int) $duration ?: 60
        ]), '[A"[6cnTDT{J[6s\'');
    }

    /**
     * @param string $token
     * @return bool
     */
    public static function csrfTokenDecode(string $token): bool
    {
        /** URI Decode */
        if (strpos($token, '%') !== false) {
            $token = rawurldecode($token) ;
        }

        /** Hack decoding link ex. Outlook */
        $token = str_replace(' ', '+', $token);
        $json  = json_decode(self::decrypt($token, '[A"[6cnTDT{J[6s\''));

        if ($json->time + $json->duration >= time()) {
            return true;
        }

        return false;
    }

    /**
     * @param string $pattern
     * @param string $value
     * @return Json
     */
    public static function matchAll(string $pattern, string $value): Json
    {
        $match   = new Json();
        $matches = [];
        preg_match_all($pattern, $value, $matches, PREG_SET_ORDER);

        foreach ($matches as $k => $array) {
            switch (count($array)) {
                case 0	: break;
                case 1  : $match->append($array[0]); break;
                case 2  : $match->append($array[1]); break;
                default :
                    array_shift($array);
                    $match->append($array);
            }
        }
        return $match;
    }

    /**
     * @param string $pattern
     * @param string $value
     * @return Json|string|null
     */
    public static function match(string $pattern, string $value)
    {
        $match  = [];
        preg_match($pattern, $value, $match);

        switch (count($match)) {
            case 0  : return null;
            case 1  : return $match[0];
            case 2  : return $match[1];
            default :
                array_shift($match);
                return new Json($match);
        }
    }

    /**
     * @param $pattern
     * @param $replacement
     * @param string $value
     * @return string
     */
    public static function replace($pattern, $replacement, string $value): string
    {
        return preg_replace($pattern, $replacement, $value);
    }

    /**
     * @param int|null $time
     * @return string
     */
    public static function time(int $time = null): string
    {
        if ($time < 0.1) {
            return round($time * 1000, 3).' Micro-secondes';
        } else if ($time < 1) {
            return round($time * 1000, 3).' Milli-secondes';
        } else if ($time) {
            return round($time, 3).' Secondes';
        } else {
            return '0';
        }
    }

    /**
     * @param int $mem
     * @return string
     */
    public static function memory(int $mem = null): string
    {
        if ($mem < 1024) {
            return $mem.' Octets';
        } else if ($mem < 1048576) {
            return round($mem/1024, 2).' Kilo-octets';
        } else if ($mem) {
            return round($mem/1048576, 2).' Mega-octets';
        } else {
            return '0';
        }
    }

    /**
     * @param string|null $prefix
     * @return string
     */
    public static function uid(string $prefix = null): string
    {
        return $prefix.md5(uniqid(rand(), true));
    }

    /**
     * @param int|null $length
     * @return string
     */
    public static function password(int $length = null): string
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
        $password = substr( str_shuffle( $chars ), 0, $length ?: 8);
        return $password;
    }

    /**
     * @param int $length
     * @param bool $readable
     * @return string
     * @throws \Exception
     */
    public static function generateCode(int $length, bool $readable = true): string
    {
        $token          = "";
        $codeAlphabet   = $readable ?
            "abcdefghjkmnpqrstuvwxyz123456789" :
            "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $max            = strlen($codeAlphabet);

        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max-1)];
        }

        return $token;
    }

    /**
     * @param string $data
     * @param string $salt
     * @return string
     */
    public static function encrypt(string $data, string $salt): string
    {
        $encrypted  = openssl_encrypt($data, 'bf-ecb', $salt, true);
        $result     = base64_encode($encrypted);

        return $result;
    }

    /**
     * @param string $data
     * @param string $salt
     * @return string
     */
    public static function decrypt(string $data, string $salt): string
    {
        $data       = base64_decode($data);
        $decrypted  = openssl_decrypt($data, 'bf-ecb', $salt, true);

        return $decrypted;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function stripSpaceBetweenTag(string $value): string
    {
        return self::trim(preg_replace('/>[\s|\t|\r|\n]+</', '><', $value));
    }

    /**
     * @param string $value
     * @param int $len
     * @return string
     */
    public static function ellipsis(string $value, int $len): string
    {
        if (strlen($value) > $len) {
            return substr($value, 0, $len).'...';
        }

        return $value;
    }

    /**
     * @param string $value
     * @param string $tag
     * @param string $replacement
     * @return string
     */
    public static function stripTag(string $value, string $tag = null, string $replacement = null): string
    {
        return self::replace('/<\/?\s*'.$tag.'[^>]*>/', $replacement ?: ' ', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function trim(string $value): string
    {
        $value = self::replace('/\n|\r|\t/', ' ', $value);
        $value = self::replace('/\s{2,}/', ' ', $value);

        return trim($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function allDigit(string $value): string
    {
        return self::replace('/[^\d]/u', '', $value);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function allText(string $value): string
    {
        $text = trim(strtolower(self::replace(
            '/[^[:alnum:]]/u',
            ' ',
            self::removeSpecialChars(self::stripTag($value))
        )));

        return $text;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function allSentence(string $value): string
    {
        $text = trim(strtolower(self::replace(
            '/[^[:alnum:]_;:\., ]/u',
            ' ',
            self::removeSpecialChars(self::stripTag($value))
        )));

        return $text;
    }

    /**
     * @param string $value
     * @return string
     */
    public static function removeSpecialChars(string $value): string
    {
        return self::replace(
            [
                '/[éèêë]/iu',
                '/[àäâ]/iu',
                '/[ùüû]/iu',
                '/[ôö]/iu',
                '/[ç]/iu',
                '/[îï]/iu',
                '/²/iu',
                '/°/iu',
                '/œ/iu'
            ], [
            'e',
            'a',
            'u',
            'o',
            'c',
            'i',
            '2',
            '.',
            'oe'
        ], self::trim($value));
    }

    /**
     * @param string $value
     * @return string
     */
    public static function removeWhiteSpace(string $value): string
    {
        return self::replace('/ /', '', $value);
    }
}