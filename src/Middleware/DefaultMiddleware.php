<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Request;
use Chukdo\Http\Response;

class DefaultMiddleware implements MiddlewareInterface
{
    /**
     * @param Request    $request
     * @param Dispatcher $delegate
     * @return Response
     */
    public function process( Request $request, Dispatcher $delegate ): Response
    {
        return new Response();
    }
}