<?php

namespace Chukdo\Bootstrap;

use Chukdo\Contracts\Exception\Handler;
use Chukdo\Helper\Http;
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
    public function render(Exception $e): void
    {
        $message  = new ExceptionMessage($e, $this->app->env());

        die($message->render());
    }
}
