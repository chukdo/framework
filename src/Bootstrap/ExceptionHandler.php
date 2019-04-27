<?php

namespace Chukdo\Bootstrap;

use Chukdo\Contracts\Exception\Handler;
use Chukdo\Helper\Http;
use Chukdo\Helper\Str;
use Chukdo\Json\JsonException;
use Exception;

/**
 * Gestionnaire par défauts des exceptions.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ExceptionHandler implements Handler
{
    /**
     * @var App
     */
    protected $app;

    /**
     * ExceptionHandler constructor.
     * @param App $app
     */
    public function __construct( App $app )
    {
        $this->app = $app;
    }

    /**
     * @param Exception $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function report( Exception $e ): void
    {
        $this->app->make('ExceptionLogger')
            ->emergency('#' . $e->getCode() . ' ' . $e->getMessage() . ' ' . $e->getFile() . '(' . $e->getLine() . ')');
    }

    /**
     * @param Exception $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function render( Exception $e ): void
    {
        $response = $this->app->make('Chukdo\Http\Response');
        $message  = new JsonException();

        $message->set('Error',
            'Error happened');

        /* Dev mode */
        if( $this->app->env() == 0 ) {
            $message->loadException($e);
        }

        switch( Str::extension(Http::server('SCRIPT_URL')) ) {
            case 'xml':
                $content     = $message->toXml()
                    ->toXmlString();
                $contentType = Http::mimeContentType('xml');
                break;
            case 'json':
                $content     = $message->toJson(true);
                $contentType = Http::mimeContentType('json');
                break;
            case 'html':
            default:
                $content     = $message->toHtml(get_class($e),
                    500);
                $contentType = Http::mimeContentType('html');
        }

        $response->status(500)
            ->header('Content-Type',
                $contentType . '; charset=utf-8')
            ->content($content)
            ->send()
            ->end();
    }

    /**
     * @param Exception $e
     */
    public function renderForConsole( Exception $e ): void
    {
        $message = new JsonException();
        $message->loadException($e)
            ->toConsole(get_class($e));
        exit;
    }
}
