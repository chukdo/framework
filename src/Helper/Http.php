<?php namespace Chukdo\Helper;

use Chukdo\Json\Json;

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
     * @param string $url
     * @param int $timeout
     * @param string $method
     * @return int
     */
    public static function getResponseCode(string $url, int $timeout = 1, string $method = 'HEAD'): int
    {
        $opts    = [
            'http'  => ['timeout' => $timeout, 'method' => $method],
            'https' => ['timeout' => $timeout, 'method' => $method]
        ];
        $context = stream_context_create($opts);
        $headers = get_headers($url, 0, $context);

        return (int) substr($headers[0], 9, 3);
    }

    /**
     * @param string $user_agent
     * @return array
     */
    public static function userAgent(string $user_agent): array
    {
        $browser    = ['name' => '', 'version' => '', 'os' => ''];
        $name       = '';

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

    /**
     * @param string $uri
     * @param string $scheme
     * @return Json
     */
    public static function uri(string $uri, string $scheme = ''): Json
    {
        /** Ajout du scheme */
        if ($scheme != '') {
            if (!Data::match("/^$scheme/", $uri)) {
                $uri = "$scheme://$uri";
            }
        }
        
        $arg   = [];
        $parse = new array_object(parse_url($uri));
        
        /** Args */
        if ($query = $parse->get('query')) {
            parse_str($query, $arg);
            
            /** Hack lorsque la query est composé de clé sans valeur on ajoute un # comme valeur */
            foreach ($arg as $k => $v) {
                if ($v == '') {
                    $arg[$k] = '#';
                }
            }
        }

        $parse->set('uri', $uri);
        $parse->set('arg', $arg);

        /** Domain */
        if ($host = $parse->get('host')) {
            $h = explode('.', $host);
            
            switch(count($h)) {
                case 3  : $parse->set('domain', $h[1]);break; 
                case 2  : $parse->set('domain', $h[0]);break;
                default : $parse->set('domain', $host);
            } 
        }
        
        /** IP */
        if ($domain = $parse->get('domain')) {
            if (filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
                filter_var($domain, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                
                $parse->set('ip', $domain);
            }
        }
        
        return $parse;
    }

    /**
     * @param string $message
     * @param string $filePath
     * @return Json
     */
    public static function parse(string $message, string $filePath = ''): Json
    {
        $bound  = false;
        $data   = [];
        $parse  = new array_object(array(
            'headers' => [],
            'cookies' => []));
        
        /** Séparation Header Body */
        $pos = strpos($message, "\r\n\r\n");
        
        /** Recuperation des entetes */
        $headers = explode("\r\n", substr($message, 0, $pos));

        /** Recuperation du body */
        $body = substr($message, $pos + 4);

        /** Recuperation de l'entete HTTP*/
        $parse->set('http', array_shift($headers));

        /** Gestion des entetes */
        foreach ($headers as $header) {
            list($name, $value) = helper_data::match('/^([a-z0-9\-]+): (.*)$/i', $header);

            switch($name) {
                case 'Set-Cookie' :
                    list($cname, $cvalue) = helper_data::match('/^([^=]+)=([^;]+)/', $value);
                    
                    /** Transformation date RFC_850 en timestamp unix */
                    if ($expires = Data::match('/expires=([^;]+)/', $value)) {
                        $date    = date_parse($expires);
                        $expires = date('U', mktime(
                            $date['hour'], 
                            $date['minute'], 
                            $date['second'], 
                            $date['month'], 
                            $date['day'], 
                            $date['year']));
                    }
                    
                    $path    = helper_data::match('/path=([^;]+)/', $value);
                    $domain  = helper_data::match('/domain=([^;]+)/', $value);
                    
                    $parse->get('cookies')->set($cname, array(
                        'value'     => $cvalue, 
                        'expires'   => $expires, 
                        'path'      => $path, 
                        'domain'    => $domain));
                    break;
                case 'Cookie' :
                    foreach (explode(';', $value) as $cookie) {
                        $cookie = explode('=', $cookie);
                        
                        if (count($cookie) == 2) {
                            $parse->get('cookies')->set(trim($cookie[0]), array('value' => rawurldecode($cookie[1])));
                        }
                    }
                    break;
                case 'Content-Type' :
                    $bound = Data::match('/boundary=(.*)/', $value);
                default :
                    $parse->get('headers')->set($name, $value);
            }
        }
        
        /** Body Multipart */
        if ($bound !== false) {
            $parse->set('body', []);
            $headers = explode('--'.$bound, $body);
            
            foreach ($headers as $header) {
                $multipart = self::parse($header, $filePath);
                
                if ($multipart->get('headers')->count() > 0) {
                    $parse->get('body')->append($multipart);
                }
            }
            
            $parse->set('type', 'multipart');
        
        /** Body simple */
        } else {
            $content        = $parse->get('headers')->get('Content-Encoding');
            $transfert      = $parse->get('headers')->get('Transfer-Encoding');
            $disposition    = $parse->get('headers')->get('Content-Disposition');
            $type           = $parse->get('headers')->get('Content-Type');
            
            $name           = Data::match('/name="([^"]+)"/', $disposition);
            $file           = Data::match('/filename="([^"]+)"/', $disposition);
 
            /** Chunked */
            if ($transfert == 'chunked') {
                $data = '';

                while ($pos = strpos($body, "\r\n")) {
                    if (($length = hexdec(substr($body, 0, $pos))) > 0) {
                        $data .= substr($body, $pos + 2, $length);
                        $body  = substr($body, $pos + 4 + $length);
                    } else {
                        break;
                    }
                }
                
                $body = $data;
            }
            
            /** Decompression */
            switch ($content) {
                case 'gzip'    : $body = Archive::ungzipString($body);break;
                case 'deflate' : $body = gzinflate($body); break;
            }
            
            /** Fichier */
            if ($file != false) {
                if (file_put_contents($filePath.$file, $body)) {
                    $parse->set('body', array('name' => $name, 'value' => $filePath.$file));
                    $parse->set('type', 'file'); 
                }
                
            /** Donnée */
            } else {
                
                /** Traitement du charset */
                if ($charset = Data::match('/charset=(.*)/', $type)) {
                    if ($charset != 'UTF-8') {
                        $body = mb_convert_encoding($body, 'UTF-8', $charset);
                    }
                }
                
                $parse->set('body', array('name' => $name, 'value' => $body));
                $parse->set('type', 'data');
            }
        }
        
        return $parse;
    }
}