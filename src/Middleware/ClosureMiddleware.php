<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Response;
use Chukdo\Json\Input;
use Closure;

class ClosureMiddleware implements MiddlewareInterface
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * ClosureMiddleware constructor.
     * @param Closure $closure
     */
    public function __construct( \Closure $closure )
    {
        $this->closure = $closure;
    }

    /**
     * @param Dispatcher $dispatcher
     * @return Response
     */
    public function process( Dispatcher $dispatcher ): Response
    {
        return ( $this->closure )($dispatcher->attribute('inputs')
            ?: $dispatcher->request()
                ->inputs(), $dispatcher->response());
    }
}