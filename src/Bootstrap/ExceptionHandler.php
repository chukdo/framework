<?php namespace Chukdo\Bootstrap;

Use \Exception;
Use \Chukdo\Helper\Data;
Use \Chukdo\Helper\Http;
Use \Chukdo\Json\Json;
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
        $ext        = Data::extension($_SERVER['SCRIPT_URL']);
        $env        = $this->app->env();
        $message    = new Json();
        $backTrace  = new Json();

        /** Dev mode */
        if ($env == 0) {
            foreach ($e->getTrace() as $trace) {
                $backTrace->append([
                    'Call' => ($trace['class'] ? $trace['class'] . $trace['type'] : '') . $trace['function'] . '()',
                    'File' => $trace['file'],
                    'Line' => $trace['line']
                ]);
            }

            $message
                ->set('Code', $e->getCode())
                ->set('Message', $e->getMessage())
                ->set('File', $e->getFile())
                ->set('Line', $e->getLine())
                ->set('Trace', $backTrace);

        /** Whooops */
        } else {
            $message->set('Message', 'Error happened');
        }

        switch($ext) {
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
                $content        = $message->toHtml('Exception');
                $contentType    = Http::mimeContentType('html');
        }

        $response
            ->status(500)
            ->header('Content-Type', $contentType)
            ->content($content)
            ->send()
            ->end();
    }

    /**
     * @param Exception $e
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function renderForConsole(Exception $e): void
    {
        $console = $this->app->make('\Chukdo\Console\Console');

        $console
            ->addRow($console->background(' ----------- EXCEPTION ----------- ', 'red'))
            ->hideBorder()
            ->flush()
            ->addHeader($console->color('Code', 'green'))
            ->addHeader($console->color('Message', 'green'))
            ->addHeader($console->color('File', 'green'))
            ->addHeader($console->color('Line', 'green'))
            ->addRow([$e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine()])
            ->flush()
            ->addHeader($console->color('Call', 'green'))
            ->addHeader($console->color('File', 'green'))
            ->addHeader($console->color('Line', 'green'));

        foreach ($e->getTrace() as $trace) {
            $console->addRow([
                ($trace['class'] ? $trace['class'] . $trace['type'] : '') . $trace['function'] . '()',
                $trace['file'],
                $trace['line']
            ]);
        }

        $console->flush();

    }
}