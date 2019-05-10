<?php

namespace Chukdo\Helper;

/**
 * Classe Cli
 * Fonctionnalités console.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Cli
{
    /**
     * @return bool
     */
    public static function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * @return string|null
     */
    public static function uri(): ?string
    {
        $inputs = self::argv();

        return isset($inputs[ 'uri' ])
            ? $inputs[ 'uri' ]
            : null;
    }

    /**
     * @return array
     */
    public static function argv(): array
    {
        $inputs = [];
        $argv   = isset($_SERVER[ 'argv' ])
            ? $_SERVER[ 'argv' ]
            : [];

        foreach ( $argv as $k => $arg ) {
            list($key, $value) = array_pad(explode('=', $arg), 2, null);

            $key = trim($key, '-');

            if ( $k > 0 ) {
                $inputs[ $key ] = $value;
            }
        }

        return $inputs;
    }

    /**
     * @return array
     */
    public static function inputs(): array
    {
        $inputs = self::argv();

        if ( isset($inputs[ 'uri' ]) ) {
            unset($inputs[ 'uri' ]);
        }

        return $inputs;
    }
}
