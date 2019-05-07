<?php

namespace Chukdo\Helper;

/**
 * Gestion des messages HTTP.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class HttpRequest
{
    /**
     * HttpRequest constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return array
     */
    public static function argv(): array
    {
        return Cli::runningInConsole()
            ? Cli::argv()
            : (array) self::server('argv');
    }

    /**
     * @param             $name
     * @param string|null $default
     * @return string|null
     */
    public static function server( $name, string $default = null ): ?string
    {
        return isset($_SERVER[ $name ])
            ? $_SERVER[ $name ]
            : $default;
    }

    /**
     * @return string|null
     */
    public static function method(): ?string
    {
        return Cli::runningInConsole()
            ? 'CLI'
            : self::request('httpverb', self::server('REQUEST_METHOD'));
    }

    /**
     * @param             $name
     * @param string|null $default
     * @return string|null
     */
    public static function request( $name, string $default = null ): ?string
    {
        $request = Cli::runningInConsole()
            ? Cli::inputs()
            : $_REQUEST;

        return isset($request[ $name ])
            ? $request[ $name ]
            : $default;
    }

    /**
     * @return bool
     */
    public static function secured(): bool
    {
        return self::server('HTTPS') || self::server('SERVER_PORT') == '443'
               || self::server('REQUEST_SCHEME') == 'https';
    }

    /**
     * @return bool
     */
    public static function ajax(): bool
    {
        return self::server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
    }

    /**
     * @return string
     */
    public static function userAgent(): string
    {
        return self::server('HTTP_USER_AGENT');
    }

    /**
     * @return string|null
     */
    public static function render(): ?string
    {
        return Cli::runningInConsole()
            ? 'cli'
            : Str::extension(self::uri());
    }

    /**
     * @return string|null
     */
    public static function uri(): ?string
    {
        return Cli::runningInConsole()
            ? Cli::uri()
            : self::server('SCRIPT_URI');
    }

    /**
     * @return array
     */
    public static function host(): array
    {
        return (array) self::server('HTTP_HOST');
    }

    /**
     * @return array
     */
    public static function cookies(): array
    {
        return (array) self::server('HTTP_COOKIE');
    }

    /**
     * @return array
     */
    public static function headers(): array
    {
        $headers = [];

        foreach ( $_SERVER as $key => $value ) {
            if ( $name = Str::match('/^HTTP_(.*)/',
                $key) ) {
                switch ( $name ) {
                    case 'HOST':
                    case 'COOKIE':
                        break;
                    default:
                        $headers[ $name ] = $value;
                }
            }
        }

        return $headers;
    }
}
