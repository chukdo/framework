<?php

namespace Chukdo\Logger\Processors;

use Chukdo\Contracts\Logger\Processor as ProcessorInterface;
use Chukdo\Helper\Http;
use Chukdo\Helper\HttpRequest;

/**
 * Ajoute la request HTTP au log.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RequestProcessor implements ProcessorInterface
{
    /**
     * Modifie / ajoute des données à un enregistrement.
     * @param array $record
     * @return array
     */
    public function processRecord( array $record ): array
    {
        $browser = Http::browser(HttpRequest::userAgent());

        $record[ 'extra' ][ 'request' ] = [
            'uri'       => HttpRequest::uri()
                ?: implode(' ',
                    HttpRequest::argv()),
            'remote'    => HttpRequest::server('REMOTE_ADDR'),
            'referer'   => HttpRequest::server('HTTP_REFERER'),
            'method'    => HttpRequest::method(),
            'useragent' => [
                'platform' => $browser[ 'platform' ]
                    ?: 'Cli',
                'browser'  => $browser[ 'browser' ],
                'version'  => $browser[ 'version' ],
                'mobile'   => $browser[ 'mobile' ],
            ],
        ];

        return $record;
    }
}
