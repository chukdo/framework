<?php

namespace Chukdo\Routing;

use Closure;
use Chukdo\Bootstrap\App;
use Chukdo\Http\Request;

/**
 * Gestion des Routes.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
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
     * @var array
     */
    protected $pattern = [];

    /**
     * @var array
     */
    protected $stack = [];

    /**
     * Router constructor.
     *
     * @param App $app
     *
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $app->make('Chukdo\Http\Request');
    }

    /**
     * @param string $key
     * @param string $regex
     *
     * @return Router
     */
    public function pattern(string $key, string $regex): self
    {
        $this->pattern[$key] = $regex;
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function get(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'GET',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function post(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'POST',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function put(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'PUT',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function delete(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'DELETE',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function any(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'ALL',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function console(string $uri, Closure $closure): Route
    {
        return $this->stack(
            'CLI',
            $uri,
            $closure
        );
    }

    /**
     * @param string  $method
     * @param string  $uri
     * @param Closure $closure
     *
     * @return Route
     */
    public function stack(string $method, string $uri, Closure $closure): Route
    {
        $route = new Route(
            $this,
            $closure
        );
        $this->stack[$method.'://'.$uri] = $route;

        return $route;
    }
}
