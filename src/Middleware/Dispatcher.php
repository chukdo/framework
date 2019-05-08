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
     * @param array $middlewares
     * @return Dispatcher
     */
    public function pipes( array $middlewares ): self
    {
        foreach ( $middlewares as $middleware ) {
            $this->pipe($middleware);
        }

        return $this;
    }

    /**
     * @param string|MiddlewareInterface $middleware
     * @return Dispatcher
     */
    public function pipe( $middleware ): self
    {
        if (is_string($middleware)) {
            if ( substr($middleware, 0, 1) == '@' ) {

                try {
                    $confMiddleware = $this->request()->conf(substr($middleware, 1));
                    $middleware = new $confMiddleware();
                } catch ( \Throwable $e ) {
                }
            } else {
                $middleware = new $middleware;
            }
        }

        if ( $middleware instanceof MiddlewareInterface ) {
            array_unshift($this->middlewares, $middleware);
            return $this;
        }

        throw new MiddlewareException('Dispatcher::pipe need Middleware or Middleware string representation');
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