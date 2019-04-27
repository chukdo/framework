<?php

namespace Chukdo\Routing;

use Chukdo\Http\HttpException;
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
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function get( string $uri, Closure $closure ): Route
    {
        return $this->stack('GET', $uri, $closure);
    }

    /**
     * @param string  $method
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function stack( string $method, string $uri, Closure $closure ): Route
    {
        $route         = new Route($method, $uri, $this->request, $closure);
        $this->stack[] = $route;

        return $route;
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function post( string $uri, Closure $closure ): Route
    {
        return $this->stack('POST', $uri, $closure);
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function put( string $uri, Closure $closure ): Route
    {
        return $this->stack('PUT', $uri, $closure);
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function delete( string $uri, Closure $closure ): Route
    {
        return $this->stack('DELETE', $uri, $closure);
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function any( string $uri, Closure $closure ): Route
    {
        return $this->stack('ALL', $uri, $closure);
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     * @return Route
     */
    public function console( string $uri, Closure $closure ): Route
    {
        return $this->stack('CLI', $uri, $closure);
    }

    public function route(): self
    {
        foreach( $this->stack as $route ) {
            if( $route->match() ) {
                $response = $route->dispatcher($this->request, $this->response);
                $validate = $route->validate();

                if( $validate->fails() ) {
                    switch( $this->request->render() ) {
                        case 'json' :
                            $response->json($validate->errors());
                            break;
                        case 'xml' :
                            $response->xml($validate->errors());
                            break;
                        default :
                            $response->html($validate->errors()
                                ->toHtml('Input Error', '#b80000'));
                    }

                    $response->send();
                }
                else {
                    $route->invoke($validate->validated(), $response);
                }

                return $this;
            }
        }

        throw new HttpException('No valid route');
    }
}
