<?php

namespace Chukdo\Middleware;

use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Throwable;

class Dispatcher
{
    /**
     * @var array
     */
    private array $middlewares = [];

    /**
     * @var array
     */
    private array $attributes = [];

    /**
     * @var int
     */
    private int $index = 0;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Response
     */
    private Response $response;

    /**
     * Dispatcher constructor.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __construct( Request $request, Response $response )
    {
        $this->request  = $request;
        $this->response = $response;
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return mixed
     */
    public function attribute( string $name, $value = null )
    {
        if ( $value === null ) {
            return $this->attributes[ $name ] ?? null;
        }

        $this->attributes[ $name ] = $value;
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
     *
     * @return Dispatcher
     */
    public function pipes( array $middlewares ): self
    {
        foreach ( $middlewares as $middleware ) {
            $this->pipe( $middleware );
        }

        return $this;
    }

    /**
     * @param string|MiddlewareInterface $middleware
     *
     * @return Dispatcher
     */
    public function pipe( $middleware ): self
    {
        if ( is_string( $middleware ) ) {
            if ( strpos( $middleware, '@' ) === 0 ) {

                try {
                    $confMiddleware = $this->request()
                                           ->conf( substr( $middleware, 1 ) );
                    $middleware     = new $confMiddleware();
                }
                catch ( Throwable $e ) {
                }
            }
            else {
                $middleware = new $middleware();
            }
        }
        if ( $middleware instanceof MiddlewareInterface ) {
            array_unshift( $this->middlewares, $middleware );

            return $this;
        }
        throw new MiddlewareException( 'Dispatcher::pipe need Middleware or Middleware string representation' );
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
    public function handle(): Response
    {
        $middleware = $this->middleware();

        $this->index++;

        if ( $middleware === null ) {
            return $this->response;
        }

        return $middleware->process( $this );
    }

    /**
     * @return MiddlewareInterface|null
     */
    private function middleware(): ?MiddlewareInterface
    {
        return $this->middlewares[ $this->index ] ?? null;
    }
}