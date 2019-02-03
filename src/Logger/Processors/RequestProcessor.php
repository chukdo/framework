<?php namespace Chukdo\Logger\Processors;

use \Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use \Chukdo\Helper\Http;

/**
 * Ajoute la request HTTP au log
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RequestProcessor implements ProcessorInterface
{
    /**
     * Modifie / ajoute des données à un enregistrement
     *
     * @param  array  $record
     * @return array
     */
    public function processRecord(array $record): array
    {
        $browser = Http::getBrowser($this->server('HTTP_USER_AGENT'));

        $record['extra']['request'] = [
            'uri'       => $this->server('REQUEST_URI'),
            'remote'    => $this->server('REMOTE_ADDR'),
            'referer'   => $this->server('HTT_REFERER'),
            'method'    => $this->server('REQUEST_METHOD'),
            'useragent' => [
                'platform'  => $browser['platform'],
                'browser'   => $browser['browser'],
                'version'   => $browser['version'],
                'mobile'    => $browser['mobile']
            ]
        ];

        return $record;
    }

    /**
     * Retourne une information de la variable $_SERVER
     *
     * @param  string   $name
     * @return string
     */
    private function server($name)
    {
        return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
    }
}