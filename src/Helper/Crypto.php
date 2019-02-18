<?php namespace Chukdo\Helper;

/**
 * Classe Str
 * Fonctionnalités de filtre sur les données
 *
 * @package		helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Crypto
{
    /**
     * @param int|null $duration
     * @return string
     */
    public static function encodeCsrf(int $duration = null): string
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
    public static function decodeCsrf(string $token): bool
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
}