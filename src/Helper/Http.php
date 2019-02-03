<?php namespace Chukdo\Helper;

use Chukdo\Helper\Data;

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
     * @param string $user_agent
     * @return array
     */
    public static function getUserAgent(string $user_agent): array
    {
        $browser    = ['name' => '', 'version' => '', 'os' => ''];
        $name       = '';

        Data::contain();

        if (empty($user_agent)) {
            return $browser;
        }

        if (preg_match('/iPad|iPhone|iPod/i', $user_agent)) {
            $browser['os'] = 'iOS';

        } elseif (preg_match('/android/i', $user_agent)) {
            $browser['os'] = 'Android';

        } elseif (preg_match('/linux/i', $user_agent)) {
            $browser['os'] = 'Linux';

        } elseif (preg_match('/macintosh|mac os x/i', $user_agent)) {
            $browser['os'] = 'Mac OS';

        } elseif (preg_match('/windows|win32/i', $user_agent)) {
            $browser['os'] = 'Windows';
        }

        if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) {
            $browser['name'] = 'Opera';
            $name = strpos($user_agent, 'OPR/') ? 'OPR/' : 'Opera';

        } elseif (strpos($user_agent, 'Edge')) {
            $browser['name'] = $name = 'Edge';

        } elseif (strpos($user_agent, 'Chrome')) {
            $browser['name'] = $name = 'Chrome';

        } elseif (strpos($user_agent, 'Safari')) {
            $browser['name'] = $name = 'Safari';

        } elseif (strpos($user_agent, 'Firefox')) {
            $browser['name'] = $name = 'Firefox';

        } elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7') || strpos($user_agent, 'Trident/7.0; rv:')) {
            $browser['name'] = 'Internet Explorer';

            if (strpos($user_agent, 'Trident/7.0; rv:')) {
                $name = 'Trident/7.0; rv:';
            } elseif (strpos($user_agent, 'Trident/7')) {
                $name = 'Trident/7';
            } else {
                $name = 'Opera';
            }
        }

        $pattern = '#' . $name . '\/*([0-9]*)#';
        $matches = [];

        if (preg_match($pattern, $user_agent, $matches)) {
            $browser['version'] = $matches[1];
        }

        return $browser;
    }
}