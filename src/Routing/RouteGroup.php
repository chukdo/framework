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
        $this->attributes = new RouteAttributes();
    }

    /**
     * @param array $middlewares
     * @return RouteGroup
     */
    public function middleware( array $middlewares ): self
    {
        $this->attributes()
            ->setMiddleware($middlewares);

        return $this;
    }

    /**
     * @return RouteAttributes
     */
    public function attributes(): RouteAttributes
    {
        return $this->attributes;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return RouteGroup
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->attributes()
            ->setValidator($validators, $errorMiddleware);

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return RouteGroup
     */
    public function prefix( ?string $prefix ): self
    {
        $this->attributes()
            ->setPrefix($prefix);

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return RouteGroup
     */
    public function namespace( ?string $namespace ): self
    {
        $this->attributes()
            ->setNamespace($namespace);

        return $this;
    }

    /**
     * @param Closure $closure
     */
    public function group( Closure $closure )
    {
        $attributes = $this->router->attributes()
            ->get();
        $this->router->attributes()
            ->add($this->attributes()
                ->get());

        ( $closure )();

        $this->router->attributes()
            ->set($attributes);
    }
}
