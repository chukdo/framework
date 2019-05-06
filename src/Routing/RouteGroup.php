<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Http\Request;
use Closure;

/**
 * Gestion des Routes.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RouteGroup
{
    /**
     * @var Router
     */
    protected $router;

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
     * RouteGroup constructor.
     * @param Router $router
     */
    public function __construct( Router $router )
    {
        $this->router = $router;
    }

    /**
     * @param array $middlewares
     * @return RouteGroup
     */
    public function middleware( array $middlewares ): self
    {
        $this->middlewares[] = $middlewares;

        return $this;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return RouteGroup
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return RouteGroup
     */
    public function prefix( ?string $prefix ): self
    {
        $this->prefix = trim($prefix, '/');

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return RouteGroup
     */
    public function namespace( ?string $namespace ): self
    {
        $this->namespace = trim($namespace, '/');

        return $this;
    }

    /**
     * @param Closure $closure
     */
    public function group( Closure $closure )
    {
        $attributes = $this->router->getAttributes();

        $this->router->setAttributes([
            'middleware'      => $this->middlewares,
            'validator'       => $this->validators,
            'errorMiddleware' => $this->errorMiddleware,
            'prefix'          => $this->prefix,
            'namespace'       => $this->namespace,
        ]);

        ( $closure )();

        $this->router->resetAttributes()
            ->setAttributes($attributes);
    }
}
