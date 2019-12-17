<?php

namespace Chukdo\Bootstrap;

use Chukdo\Contracts\Exception\Handler;
use Throwable;

/**
 * Gestionnaire par défauts des exceptions.
 *
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
    protected App $app;

    /**
     * ExceptionHandler constructor.
     *
     * @param App $app
     */
    public function __construct( App $app )
    {
        $this->app = $app;
    }

    /**
     * @param Throwable $e
     */
    public function render( Throwable $e ): void
    {
        $message = new ExceptionMessage( $e, (int)$this->app->env() );
        $message->render();
        exit;
    }

    /**
     * @param Throwable $e
     */
    public function report( Throwable $e ): void
    {
        try {
            $this->app->make( 'ExceptionLogger' )
                      ->emergency( '#' . $e->getCode() . ' ' . $e->getMessage() . ' ' . $e->getFile() . '(' . $e->getLine() . ')' );
        } catch ( Throwable $e ) {
        }
    }
}
