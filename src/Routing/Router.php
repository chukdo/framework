<?php

namespace Chukdo\Routing;

use Chukdo\Http\HttpException;
use Chukdo\Http\Response;
use Chukdo\Middleware\AppMiddleware;
use Closure;
use Chukdo\Bootstrap\App;
use Chukdo\Http\Request;

/**
 * Gestion des Routes.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Router extends RouteAttribute
{
    /**
     * @var App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * @var Closure
     */
    protected $fallback = null;

    /**
     * @var array
     */
    protected $group = [];

    /**
     * Router constructor.
     * @param App $app
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct( App $app )
    {
        $this->app      = $app;
        $this->request  = $app->make('Chukdo\Http\Request');
        $this->response = $this->app->make('Chukdo\Http\Response');
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function get( string $uri, $closure ): Route
    {
        return $this->stack('GET', $uri, $closure);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function stack( string $method, string $uri, $closure ): Route
    {
        if( $closure instanceof Closure ) {
            $appMiddleware = new AppMiddleware($closure);
        }
        elseif( is_string($closure) ) {
            // namespace
            // App\Controlers\xxx
        }
        else {
            throw new HttpException('Router stack need a Closure or a String');
        }

        $route = new Route($method, $uri, $this->request, $appMiddleware);
        $route->middleware($this->middlewares);
        $route->validator($this->validators, $this->errorMiddleware);
        $route->prefix($this->prefix);
        $route->namespace($this->namespace);

        $this->stack[] = $route;

        return $route;
    }

    /**
     * @param Closure $closure
     * @return RouteGroup
     */
    public function group( Closure $closure ): RouteGroup
    {
        $group         = new RouteGroup($this, $this->request, $closure);
        $this->group[] = $group;

        return $group;
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function post( string $uri, $closure ): Route
    {
        return $this->stack('POST', $uri, $closure);
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function put( string $uri, $closure ): Route
    {
        return $this->stack('PUT', $uri, $closure);
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function delete( string $uri, $closure ): Route
    {
        return $this->stack('DELETE', $uri, $closure);
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function any( string $uri, $closure ): Route
    {
        return $this->stack('ALL', $uri, $closure);
    }

    /**
     * @param string $uri
     * @param        $closure
     * @return Route
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function console( string $uri, $closure ): Route
    {
        return $this->stack('CLI', $uri, $closure);
    }

    /**
     * @return Response
     */
    public function route(): Response
    {
        foreach( $this->group as $group ) {
            $group->route();
        }

        foreach( $this->stack as $route ) {
            if( $route->match() ) {
                return $route->dispatcher($this->response)
                    ->send();
            }
        }

        if( $this->fallback instanceof Closure ) {
            ($this->fallback)($this->request, $this->response);
        }
        else {
            throw new HttpException('No valid route');
        }
    }

    /**
     * @param Closure $fallback
     * @return Router
     */
    public function fallback( Closure $fallback ): self
    {
        $this->fallback = $fallback;

        return $this;
    }
}
