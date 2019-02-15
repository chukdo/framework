<?php namespace Chukdo\Helper;

use Chukdo\Http\Url;

/**
 * Gestion des messages HTTP
 *
 * @package		helper
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
final class Http
{
    /**
     * Http constructor.
     */
    private function __construct() {}

    /**
     * @param string $name
     * @return string
     */
    public static function mimeContentType(string $name): string
    {
        $mimeTypes = [
            'txt'   => 'text/plain',
            'htm'   => 'text/html',
            'html'  => 'text/html',
            'php'   => 'text/html',
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'json'  => 'application/json',
            'xml'   => 'application/xml',
            'png'   => 'image/png',
            'jpe'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'jpg'   => 'image/jpeg',
            'gif'   => 'image/gif',
            'bmp'   => 'image/bmp',
            'ico'   => 'image/vnd.microsoft.icon',
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'svg'   => 'image/svg+xml',
            'zip'   => 'application/zip',
            'rar'   => 'application/x-rar-compressed',
            'mp3'   => 'audio/mpeg',
            'pdf'   => 'application/pdf',
            'psd'   => 'image/vnd.adobe.photoshop',
            'ai'    => 'application/postscript',
            'eps'   => 'application/postscript',
            'ps'    => 'application/postscript',
            'ttf'   => 'font/ttf',
            'doc'   => 'application/msword',
            'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'rtf'   => 'application/rtf',
            'xls'   => 'application/vnd.ms-excel',
            'ppt'   => 'application/vnd.ms-powerpoint',
            'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        ];

        $ext = Data::extension($name);

        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];

        } else {
            return 'application/octet-stream';
        }
    }

    /**
     * @return bool
     */
    public static function acceptDeflateEncoding(): bool
    {
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            return strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function acceptGzipEncoding(): bool
    {
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            return strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false;
        }

        return false;
    }

    /**
     * @return Url
     */
    public static function getUrl(): Url
    {
        return new Url($_SERVER['URI']);
    }

    /**
     * @param string|null $ua
     * @return array
     */
    public static function getUserAgent(string $ua = null): array
    {
        $browser = [
            'platform'  => null,
            'browser'   => null,
            'mobile'    => null,
            'version'   => null,
            'bot'       => null
        ];

        $ua             = strtolower($ua);
        $browser['bot'] = Data::match('/baiduspider|googlebot|yandexbot|bingbot|lynx|wget|curl/', $ua);
        $is             = function($contain, $name = false) use ($ua, &$browser) {
            if (Data::contain($ua, $contain)) {
                $browser['browser'] = $name ?: $contain;
                $browser['version'] = Data::match('/'.$contain.'[\/\s](\d+)/', $ua);
                return true;
            }

            return false;
        };

        /** Browser & Version */
        if (!$is('firefox')) {
            if (!$is('edge', 'msie')) {
                if (!$is('msie')) {
                    if (!$is('trident', 'msie')) {
                        if (!$is('opera')) {
                            if (!$is('opr', 'opera')) {
                                if (!$is('chromium', 'chrome')) {
                                    if (!$is('chrome')) {
                                        $is('safari');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /** Platform */
        if (Data::contain($ua, 'windows')) {
            $browser['platform'] = 'windows';
        } else if (Data::contain($ua, 'linux')) {
            $browser['platform'] = 'linux';
        } else if (Data::contain($ua, 'mac')) {
            $browser['platform'] = 'osx';
        }

        /** Mobile */
        if (Data::contain($ua, 'ipad') || Data::contain($ua, 'iphone')) {
            $browser['mobile'] = 'ios';
        } else if (Data::contain($ua, 'android')) {
            $browser['mobile'] = 'android';
        }

        return $browser;
    }
}