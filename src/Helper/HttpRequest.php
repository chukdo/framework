<?php

namespace Chukdo\Helper;

use Chukdo\Http\Url;
use Chukdo\Xml\Xml;

/**
 * Gestion des messages HTTP.
 *
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
     * @return string|null
     */
    public static function method(): ?string
    {
        return Cli::runningInConsole()
            ? 'CLI'
            : self::request( 'httpverb', self::server( 'REQUEST_METHOD' ) );
    }

    /**
     * @param             $name
     * @param string|null $default
     *
     * @return string|null
     */
    public static function request( $name, string $default = null ): ?string
    {
        $request = self::all();

        return $request[ $name ] ?? $default;
    }

    /**
     * @return array
     */
    public static function all(): array
    {
        if ( Cli::runningInConsole() ) {
            return Cli::inputs();
        }

        if ( !empty( $_REQUEST ) ) {
            return $_REQUEST;
        }

        $data   = self::input();
        $isJson = strpos( $data, '{' ) === 0;
        $isXml  = strpos( $data, '<' ) === 0;

        if ( $data && $isJson ) {
            return json_decode( $data, true, 512, JSON_THROW_ON_ERROR );
        }

        if ( $data && $isXml ) {
            $xml = Xml::loadFromString( $data );

            return $xml->toArray();
        }

        return [];
    }

    /**
     * @return string
     */
    public static function input(): string
    {
        static $input = null;

        if ( $input ) {
            return $input;
        }

        return $input = (string) file_get_contents( 'php://input' );
    }

    /**
     * @param string      $name
     * @param string|null $default
     *
     * @return string|null
     */
    public static function server( string $name, string $default = null ): ?string
    {
        return $_SERVER[ $name ] ?? $default;
    }

    /**
     * @return bool
     */
    public static function secured(): bool
    {
        return self::server( 'HTTPS' ) || self::server( 'SERVER_PORT' ) === '443' || self::server( 'REQUEST_SCHEME' ) === 'https';
    }

    /**
     * @return bool
     */
    public static function ajax(): bool
    {
        return self::server( 'HTTP_X_REQUESTED_WITH' ) === 'XMLHttpRequest';
    }

    /**
     * @return string|null
     */
    public static function userAgent(): ?string
    {
        return self::server( 'HTTP_USER_AGENT' );
    }

    /**
     * @return string|null
     */
    public static function render(): ?string
    {
        if ( Cli::runningInConsole() ) {
            return 'cli';
        }

        if ( $uri = self::uri() ) {
            return Str::extension( $uri );
        }

        return null;
    }

    /**
     * @return string|null
     */
    public static function uri(): ?string
    {
        return Cli::runningInConsole()
            ? Cli::uri()
            : self::server( 'SCRIPT_URI' );
    }

    /**
     * @return string|null
     */
    public static function host(): ?string
    {
        return self::server( 'HTTP_HOST' );
    }

    /**
     * @param string|null $default
     *
     * @return string|null
     */
    public static function tld( string $default = null ): ?string
    {
        return ( new Url( self::uri() ) )->getTld()
            ?: $default;
    }

    /**
     * @param string|null $default
     *
     * @return string|null
     */
    public static function domain( string $default = null ): ?string
    {
        return ( new Url( self::uri() ) )->getDomain()
            ?: $default;
    }

    /**
     * @param string|null $default
     *
     * @return string|null
     */
    public static function subDomain( string $default = null ): ?string
    {
        return ( new Url( self::uri() ) )->getSubDomain()
            ?: $default;
    }

    /**
     * @return array
     */
    public static function cookies(): array
    {
        return (array) self::server( 'HTTP_COOKIE' );
    }

    /**
     * @return array
     */
    public static function headers(): array
    {
        $headers = [];
        foreach ( $_SERVER as $key => $value ) {
            if ( $name = Str::matchOne( '/^HTTP_(.*)/', $key ) ) {
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
