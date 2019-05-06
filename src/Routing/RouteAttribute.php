<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;

/**
 * Gestion des groupes de Routes.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RouteAttribute
{
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

    public function resetAttributes(): self
    {
        $this->middlewares     = [];
        $this->validators      = [];
        $this->errorMiddleware = null;
        $this->prefix          = '';
        $this->namespace       = '';

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return [
            'middleware'      => $this->middlewares,
            'validator'       => $this->validators,
            'errorMiddleware' => $this->errorMiddleware,
            'prefix'          => $this->prefix,
            'namespace'       => $this->namespace,
        ];
    }

    /**
     * @param array $attributes
     * @return RouteAttribute
     */
    public function setAttributes( array $attributes ): self
    {
        $initAttributes = [
            'middleware'      => [],
            'validator'       => [],
            'errorMiddleware' => null,
            'prefix'          => '',
            'namespace'       => '',
        ];

        $attributes = array_merge($initAttributes, $attributes);

        $this->middleware($attributes[ 'middleware' ]);
        $this->validator($attributes[ 'validator' ], $attributes[ 'errorMiddleware' ]);
        $this->prefix($attributes[ 'prefix' ]);
        $this->namespace($attributes[ 'namespace' ]);

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
     * @param string|null $prefix
     * @return Route
     */
    public function prefix( ?string $prefix ): self
    {
        $prefix = trim($prefix, '/');

        if( strlen($prefix) > 0 ) {
            $this->prefix .= '/' . $prefix;
        }

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return Route
     */
    public function namespace( ?string $namespace ): self
    {
        $namespace = trim($namespace, '/');

        if( strlen($namespace) > 0 ) {
            $this->namespace .= '/' . $namespace;
        }

        return $this;
    }
}
