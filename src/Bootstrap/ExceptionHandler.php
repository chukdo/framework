<?php namespace Chukdo\Bootstrap;

Use \Exception;
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
        //$this->app->make('Response'); \Chukdo\Http\Response
        var_dump($e->getMessage());
        var_dump($e->getTraceAsString());
        // conf ENV oupsss!!
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