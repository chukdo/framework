<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Request;
use Chukdo\Http\Response;

class Dispatcher
{
    /**
     * @var array
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var Response
     */
    private $response;

    /**
     * Dispatcher constructor.
     */
    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * @return Response
     */
    public function response(): Response
    {
        return $this->response;
    }

    /**
     * @param array $middleswares
     * @return Dispatcher
     */
    public function pipes( array $middleswares ): self
    {
        foreach( $middleswares as $middlesware ) {
            $this->pipe($middlesware);
        }

        return $this;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return Dispatcher
     */
    public function pipe( MiddlewareInterface $middleware ): self
    {
        array_unshift($this->middlewares, $middleware);

        return $this;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function handle( Request $request ): Response
    {
        $middleware = $this->getMiddleware();
        $this->index++;

        if( is_null($middleware) ) {
            return $this->response;
        }

        return $middleware->process($request, $this);
    }

    /**
     * @return callable|null
     */
    private function getMiddleware()
    {
        if( isset($this->middlewares[ $this->index ]) ) {
            return $this->middlewares[ $this->index ];
        }

        return null;
    }
}