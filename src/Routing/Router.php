<?php

namespace Chukdo\Routing;

use Chukdo\Bootstrap\ServiceException;
use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Helper\Is;
use Chukdo\Http\Response;
use Chukdo\Middleware\ClosureMiddleware;
use Chukdo\Middleware\ControlerMiddleware;
use Closure;
use Chukdo\Bootstrap\App;
use Chukdo\Http\Request;
use ReflectionException;

/**
 * Gestion des Routes.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Router
{
    /**
     * @var App
     */
    protected App $app;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Response
     */
    protected Response $response;

    /**
     * @var array
     */
    protected array $stack = [];

    /**
     * @var RouteAttributes
     */
    protected RouteAttributes $attributes;

    /**
     * @var Closure
     */
    protected Closure $fallback;

    /**
     * Router constructor.
     *
     * @param App $app
     *
     * @throws ServiceException
     * @throws ReflectionException
     */
    public function __construct( App $app )
    {
        $this->app        = $app;
        $this->request    = $app->make( Request::class );
        $this->response   = $this->app->make( Response::class );
        $this->attributes = new RouteAttributes();
        $this->fallback   = static function()
        {
            throw new RouteException( 'No valid route' );
        };
    }

    /**
     * @param array $middlewares
     *
     * @return RouteGroup
     */
    public function middleware( array $middlewares ): RouteGroup
    {
        return ( new RouteGroup( $this ) )->middleware( $middlewares );
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     *
     * @return RouteGroup
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): RouteGroup
    {
        return ( new RouteGroup( $this ) )->validator( $validators, $errorMiddleware );
    }

    /**
     * @param string|null $prefix
     *
     * @return RouteGroup
     */
    public function prefix( ?string $prefix ): RouteGroup
    {
        return ( new RouteGroup( $this ) )->prefix( $prefix );
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
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function get( string $uri, $closure ): Route
    {
        return $this->stack( 'GET', $uri, $closure );
    }

    /**
     * @param string $method
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function stack( string $method, string $uri, $closure ): Route
    {
        if ( $closure instanceof Closure ) {
            $appMiddleware = new ClosureMiddleware( $closure );

        }
        elseif ( Is::string( $closure ) ) {
            $appMiddleware = new ControlerMiddleware( $closure );

        }
        else {
            throw new RouteException( 'Router stack need a Closure or a String controler@action' );
        }
        $route = new Route( $method, $uri, $this->request, $appMiddleware );

        $route->attributes()
              ->set( $this->attributes()
                          ->get() );
        $this->stack[] = $route;

        return $route;
    }

    /**
     * @return RouteAttributes
     */
    public function attributes(): RouteAttributes
    {
        return $this->attributes;
    }

    /**
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function post( string $uri, $closure ): Route
    {
        return $this->stack( 'POST', $uri, $closure );
    }

    /**
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function put( string $uri, $closure ): Route
    {
        return $this->stack( 'PUT', $uri, $closure );
    }

    /**
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function delete( string $uri, $closure ): Route
    {
        return $this->stack( 'DELETE', $uri, $closure );
    }

    /**
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function any( string $uri, $closure ): Route
    {
        return $this->stack( 'ALL', $uri, $closure );
    }

    /**
     * @param string $uri
     * @param        $closure
     *
     * @return Route
     */
    public function console( string $uri, $closure ): Route
    {
        return $this->stack( 'CLI', $uri, $closure );
    }

    /**
     * @return Response
     */
    public function route(): Response
    {
        foreach ( $this->stack as $route ) {
            if ( $route->match() ) {
                return $route->dispatcher( $this->response )
                             ->send();
            }
        }

        return ( $this->fallback )( $this->request, $this->response );
    }

    /**
     * @param Closure $fallback
     *
     * @return Router
     */
    public function fallback( Closure $fallback ): self
    {
        $this->fallback = $fallback;

        return $this;
    }
}
