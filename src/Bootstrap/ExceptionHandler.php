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
        //$this->app->make('Response');
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
            ->addHeader($console->background('Exception', 'red'))
            ->flushAll();

        $console->setHeaders([
            'Code',
            'Message',
            'File',
            'Line',
        ])->addRow([
            $e->getCode(),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        ])->flushAll();

        $console->setHeaders([
            'Call',
            'File',
            'Line'
        ]);

        foreach ($e->getTrace() as $trace) {
            $console->addRow([
                ($trace['class'] ? $trace['class'] . $trace['type'] : '') . $trace['function'] . '()',
                $trace['file'],
                $trace['line']
            ]);
        }

        $console->flushAll();

    }
}