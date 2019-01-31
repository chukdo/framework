<?php namespace Chukdo\Logger\Processors;

use \Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use \Chukdo\Helper\Http;

/**
 * Ajoute le debug_backtrace au log
 *
 * @copyright 	licence MIT, Copyright (C) 2015 Domingo
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
    public function processRecord(array $record)
    {
        $browser = Http::getBrowser($this->server('HTTP_USER_AGENT'));

        $record['extra']['request'] = array(
            'uri'       => $this->server('REQUEST_URI'),
            'remote'    => $this->server('REMOTE_ADDR'),
            'referer'   => $this->server('HTT_REFERER'),
            'method'    => $this->server('REQUEST_METHOD'),
            'useragent' => array(
                'platform'  => $browser['platform'],
                'browser'   => $browser['browser'],
                'version'   => $browser['version'],
                'mobile'    => $browser['mobile']
            )
        );

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