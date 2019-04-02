<?php

namespace Chukdo\Contracts\Exception;

use Exception;

/**
 * Interface de Gestionnaires des exception.
 *
 * @version       1.0.0
 *
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 *
 * @since         08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Handler
{
    /**
     * @param Exception $e
     */
    public function report( Exception $e ): void;

    /**
     * @param Exception $e
     */
    public function render( Exception $e ): void;

    /**
     * @param Exception $e
     */
    public function renderForConsole( Exception $e ): void;
}
