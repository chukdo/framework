<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
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
class RouterGroup
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * @var string
     */
    protected $namespace = '';

    /**
     * @var ErrorMiddlewareInterface
     */
    protected $errorMiddleware = null;

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * RouterGroup constructor.
     * @param Router  $router
     * @param Closure $closure
     */
    public function __construct( Router $router, Closure $closure )
    {
        $this->router  = $router;
        $this->closure = $closure;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return Router
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }

    /**
     * @param array $middlewares
     * @return Route
     */
    public function middleware( array $middlewares ): self
    {
        foreach( $middlewares as $middleware ) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return Route
     */
    public function prefix( ?string $prefix ): self
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return Route
     */
    public function namespace( ?string $namespace ): self
    {
        $this->namespace = trim($namespace, '/');

        return $this;
    }

    /**
     * @return RouterGroup
     */
    public function route(): self
    {
        $this->router->attributes([
            'middleware'      => $this->middlewares,
            'validator'       => $this->validators,
            'errorMiddleware' => $this->errorMiddleware,
            'prefix'          => $this->prefix,
            'namespace'       => $this->namespace,
        ]);
        ($this->closure)();
        $this->router->attributes([]);

        return $this;
    }
}
