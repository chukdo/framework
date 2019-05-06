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
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * Dispatcher constructor.
     * @param Request  $request
     * @param Response $response
     */
    public function __construct( Request $request, Response $response )
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
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
        foreach ( $middleswares as $middlesware ) {
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
     * @return Response
     */
    public function handle(): Response
    {
        $middleware = $this->getMiddleware();
        $this->index++;

        if ( is_null($middleware) ) {
            return $this->response;
        }

        return $middleware->process($this);
    }

    /**
     * @return callable|null
     */
    private function getMiddleware()
    {
        if ( isset($this->middlewares[ $this->index ]) ) {
            return $this->middlewares[ $this->index ];
        }

        return null;
    }
}