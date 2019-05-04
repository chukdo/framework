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
class Router
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
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function get( string $uri, $closure ): Route
    {
        return $this->stack('GET', $uri, $closure);
    }

    /**
     * @param string         $method
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function stack( string $method, string $uri, $closure ): Route
    {
        if( $closure instanceof Closure ) {
            $appMiddleware = new AppMiddleware($closure);
        }
        elseif( is_string($closure) ) {

        }
        else {
            throw new HttpException('Router stack need a Closure or a String');
        }

        $route         = new Route($method, $uri, $this->request, $appMiddleware);
        $this->stack[] = $route;

        return $route;
    }

    /**
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function post( string $uri, $closure ): Route
    {
        return $this->stack('POST', $uri, $closure);
    }

    /**
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function put( string $uri, $closure ): Route
    {
        return $this->stack('PUT', $uri, $closure);
    }

    /**
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function delete( string $uri, $closure ): Route
    {
        return $this->stack('DELETE', $uri, $closure);
    }

    /**
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function any( string $uri, $closure ): Route
    {
        return $this->stack('ALL', $uri, $closure);
    }

    /**
     * @param string         $uri
     * @param Closure|string $closure
     * @return Route
     */
    public function console( string $uri, $closure ): Route
    {
        return $this->stack('CLI', $uri, $closure);
    }

    /**
     * @return Router
     */
    public function route(): Response
    {
        foreach( $this->stack as $route ) {
            if( $route->match() ) {
                return $route->dispatcher($this->response);
            }
        }

        throw new HttpException('No valid route');
    }
}
