<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
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
     * @var RouteAttributes
     */
    protected $attributes;

    /**
     * RouteGroup constructor.
     * @param Router $router
     */
    public function __construct( Router $router )
    {
        $this->router     = $router;
        $this->attributes = new RouteAttributes($router->request());
    }

    /**
     * @return RouteGroup
     */
    public function resetAttributes(): self
    {
        $this->attributes->resetAttributes();

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes->getAttributes();
    }

    /**
     * @param array $attributes
     * @return Router
     */
    public function setAttributes( array $attributes ): self
    {
        $this->attributes->setAttributes($attributes);

        return $this;
    }

    /**
     * @param array $attributes
     * @return RouteGroup
     */
    public function addAttributes( array $attributes ): self
    {
        $this->attributes->addAttributes($attributes);

        return $this;
    }

    /**
     * @param array $middlewares
     * @return RouteGroup
     */
    public function middleware( array $middlewares ): self
    {
        $this->attributes->middleware($middlewares);

        return $this;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return RouteGroup
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->attributes->validator($validators, $errorMiddleware);

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return RouteGroup
     */
    public function prefix( ?string $prefix ): self
    {
        $this->attributes->prefix($prefix);

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return RouteGroup
     */
    public function namespace( ?string $namespace ): self
    {
        $this->attributes->namespace($namespace);

        return $this;
    }

    /**
     * @param Closure $closure
     */
    public function group( Closure $closure )
    {
        $attributes = $this->router->getAttributes();
        $this->router->addAttributes($this->attributes->getAttributes());

        ( $closure )();

        $this->router->setAttributes($attributes);
    }
}
