<?php namespace Chukdo\Helper;

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
     * @param string $ua
     * @return array
     */
    public static function getUserAgent(string $ua): array
    {
        $ua = strtolower($ua);

        $browser = [
            'platform'  => null,
            'browser'   => null,
            'mobile'    => null,
            'version'   => null,
            'bot'       => Data::match('/baiduspider|googlebot|yandexbot|bingbot|lynx|wget|curl/', $ua)
        ];

        $is = function($contain, $name = false) use ($ua, &$browser) {
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