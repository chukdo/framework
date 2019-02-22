<?php namespace Chukdo\Routing;

Use \Closure;
Use \Chukdo\Bootstrap\App;

/**
 * Gestion des Routes
 *
 * @package		Routing
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Router
{
    /**
     * @var App $app
     */
    protected $app;

    /**
     * Router constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function get(string $uri, Closure $callback): Route
    {

    }

    public function post(string $uri, Closure $callback): Route
    {

    }

    public function put(string $uri, Closure $callback): Route
    {

    }

    public function delete(string $uri, Closure $callback): Route
    {

    }

    public function any(string $uri, Closure $callback): Route
    {

    }

    public function console(string $uri, Closure $callback): Route
    {

    }
}