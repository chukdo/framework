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
     * @param Exception $e
     */
    public function report(Exception $e): void
    {
        // app make logger
    }

    /**
     * @param Exception $e
     */
    public function render(Exception $e): void
    {
        // app make http
    }

    /**
     * @param Exception $e
     */
    public function renderForConsole(Exception $e): void
    {
        // app make console
    }
}