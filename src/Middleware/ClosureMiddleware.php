<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Response;
use Closure;

class ClosureMiddleware implements MiddlewareInterface
{
    /**
     * @var \Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * ClosureMiddleware constructor.
     * @param Closure $closure
     */
    public function __construct( \Closure $closure )
    {
        $this->closure = $closure;
    }

    /**
     * @param Dispatcher $delegate
     * @return Response
     */
    public function process( Dispatcher $delegate ): Response
    {
        return ( $this->closure )();
    }
}