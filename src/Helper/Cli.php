<?php

namespace Chukdo\Helper;
/**
 * Classe Cli
 * Fonctionnalités console.
 *
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
        return PHP_SAPI === 'cli';
    }

    /**
     * @return string|null
     */
    public static function uri(): ?string
    {
        $inputs = self::argv();

        return $inputs[ 0 ] ?? null;
    }

    /**
     * @return array
     */
    public static function argv(): array
    {
        $key    = 0;
        $inputs = [];
        $argv   = $_SERVER[ 'argv' ] ?? [];
        foreach ( $argv as $k => $arg ) {
            if ( strpos( $arg, '-' ) === 0 ) {
                $key = trim( $arg, '-' );
            }
            else {
                $inputs[ $key ] = $arg;
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
        if ( isset( $inputs[ 0 ] ) ) {
            unset( $inputs[ 0 ] );
        }

        return $inputs;
    }
}
