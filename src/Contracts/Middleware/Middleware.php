<?php

namespace Chukdo\Contracts\Middleware;

use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Chukdo\Middleware\Dispatcher;

/**
 * Interface des middlewares.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Middleware
{
    /**
     * @param Request  $request
     * @param Delegate $delegate
     * @return Response
     */
    public function process( Request $request, Dispatcher $delegate ): Response;
}
