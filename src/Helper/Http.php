<?php

namespace Chukdo\Helper;

use Chukdo\Http\Url;

/**
 * Gestion des messages HTTP.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Http
{
    protected static array $mimeTypes = [
        'txt'  => 'text/plain',
        'htm'  => 'text/html',
        'html' => 'text/html',
        'php'  => 'text/html',
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'json' => 'application/json',
        'xml'  => 'application/xml',
        'png'  => 'image/png',
        'jpe'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg'  => 'image/jpeg',
        'gif'  => 'image/gif',
        'bmp'  => 'image/bmp',
        'ico'  => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif'  => 'image/tiff',
        'svg'  => 'image/svg+xml',
        'zip'  => 'application/zip',
        'rar'  => 'application/x-rar-compressed',
        'mp3'  => 'audio/mpeg',
        'pdf'  => 'application/pdf',
        'psd'  => 'image/vnd.adobe.photoshop',
        'ai'   => 'application/postscript',
        'eps'  => 'application/postscript',
        'ps'   => 'application/postscript',
        'ttf'  => 'font/ttf',
        'doc'  => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'rtf'  => 'application/rtf',
        'xls'  => 'application/vnd.ms-excel',
        'ppt'  => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    /**
     * HttpRequest constructor.
     */
    private function __construct()
    {
    }

    /**
     * @param string $contentType
     *
     * @return string
     */
    public static function contentTypeToExt( string $contentType ): string
    {
        $contentType  = strtolower( $contentType );
        $contentTypes = array_flip( self::$mimeTypes );

        return $contentTypes[ $contentType ] ?? 'txt';
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public static function extToContentType( string $name ): string
    {
        $ext = Str::extension( $name );

        if ( array_key_exists( $ext, self::$mimeTypes ) ) {
            return self::$mimeTypes[ $ext ];
        }

        return 'application/octet-stream';
    }

    /**
     * @param string $ua
     *
     * @return array
     */
    public static function browser( string $ua ): array
    {
        $browser          = [
            'platform' => null,
            'browser'  => null,
            'mobile'   => null,
            'version'  => null,
            'bot'      => null,
        ];
        $ua               = strtolower( $ua );
        $browser[ 'bot' ] = Str::matchOne( '/baiduspider|googlebot|yandexbot|bingbot|lynx|wget|curl/', $ua );
        $is               = static function( $contain, $name = false ) use ( $ua, &$browser )
        {
            if ( Str::contain( $ua, $contain ) ) {
                $browser[ 'browser' ] = $name
                    ?: $contain;
                $browser[ 'version' ] = Str::matchOne( '/' . $contain . '[\/\s](\d+)/', $ua );

                return true;
            }

            return false;
        };

        /** Browser & Version */
        if ( !$is( 'firefox' ) && !$is( 'edge', 'msie' ) && !$is( 'msie' ) && !$is( 'trident', 'msie' ) && !$is( 'opera' ) && !$is( 'opr', 'opera' ) && !$is( 'chromium', 'chrome' ) && !$is( 'chrome' ) ) {
            $is( 'safari' );
        }

        /** Platform */
        if ( Str::contain( $ua, 'windows' ) ) {
            $browser[ 'platform' ] = 'windows';

        }
        elseif ( Str::contain( $ua, 'linux' ) ) {
            $browser[ 'platform' ] = 'linux';

        }
        elseif ( Str::contain( $ua, 'mac' ) ) {
            $browser[ 'platform' ] = 'osx';
        }

        /** Mobile */
        if ( Str::contain( $ua, 'ipad' ) || Str::contain( $ua, 'iphone' ) ) {
            $browser[ 'mobile' ] = 'ios';

        }
        elseif ( Str::contain( $ua, 'android' ) ) {
            $browser[ 'mobile' ] = 'android';
        }

        return $browser;
    }
}