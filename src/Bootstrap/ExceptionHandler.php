<?php namespace Chukdo\Bootstrap;

Use \Exception;
Use \Chukdo\Helper\Str;
Use \Chukdo\Helper\Http;
Use \Chukdo\Json\JsonException;
Use \Chukdo\Contracts\Exception\Handler;

/**
 * Gestionnaire par défauts des exceptions
 *
 * @package 	Contracts
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class ExceptionHandler Implements Handler
{
    /**
     * @var App $app
     */
    protected $app;

    /**
     * ExceptionHandler constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Exception $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function report(Exception $e): void
    {
        $this
            ->app
            ->make('ExceptionLogger')
            ->emergency('#'. $e->getCode() . ' ' . $e->getMessage() . ' ' . $e->getFile() . '(' . $e->getLine() . ')');
    }

    /**
     * @param Exception $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function render(Exception $e): void
    {
        $response   = $this->app->make('\Chukdo\Http\Response');
        $message    = new JsonException();

        $message->set('Message', 'Error happened');

        /** Dev mode */
        if ($this->app->env() == 0) {
            $message->loadException($e);
        }

        switch(Str::extension($_SERVER['SCRIPT_URL'])) {
            case 'xml' :
                $content        = $message->toXml()->toXmlString();
                $contentType    = Http::mimeContentType('xml');
                break;
            case 'json' :
                $content        = $message->toJson(true);
                $contentType    = Http::mimeContentType('json');
                break;
            case 'html' :
            default :
                $content        = $message->toHtml(get_class($e), 500);
                $contentType    = Http::mimeContentType('html');
        }

        $response
            ->status(500)
            ->header('Content-Type', $contentType. '; charset=utf-8')
            ->content($content)
            ->send()
            ->end();
    }

    /**
     * @param Exception $e
     */
    public function renderForConsole(Exception $e): void
    {
        $message = new JsonException();
        $message->loadException($e)->toConsole(get_class($e));
        exit;
    }
}