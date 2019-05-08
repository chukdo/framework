<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;

/**
 * Gestion des attributs d'une Route.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class RouteAttributes
{
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
     * @var array
     */
    protected $middlewares = [];

    /**
     * RouteAttributes constructor.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * @return RouteAttributes
     */
    public function reset(): self
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
    public function get(): array
    {
        return [
            'middleware'      => $this->getMiddleware(),
            'validator'       => $this->getValidator(),
            'errorMiddleware' => $this->getErrorMiddleware(),
            'prefix'          => $this->getPrefix(),
            'namespace'       => $this->getNamespace(),
        ];
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middlewares;
    }

    /**
     * @return array
     */
    public function getValidator(): array
    {
        return $this->validators;
    }

    /**
     * @return ErrorMiddlewareInterface|null
     */
    public function getErrorMiddleware(): ?ErrorMiddlewareInterface
    {
        return $this->errorMiddleware;
    }

    /**
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return RouteAttributes
     */
    public function setErrorMiddleware( ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     * @return RouteAttributes
     */
    public function setPrefix( ?string $prefix ): self
    {
        $prefix = trim($prefix, '/');

        if ( strlen($prefix) > 0 ) {
            $this->prefix .= '/' . $prefix;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string|null $namespace
     * @return RouteAttributes
     */
    public function setNamespace( ?string $namespace ): self
    {
        $namespace = trim($namespace, '/');

        if ( strlen($namespace) > 0 ) {
            $this->namespace .= '/' . $namespace;
        }

        return $this;
    }

    /**
     * @param array $attributes
     * @return RouteAttributes
     */
    public function set( array $attributes ): self
    {
        $this->reset()
            ->add($attributes);

        return $this;
    }

    /**
     * @param array $attributes
     * @return RouteAttributes
     */
    public function add( array $attributes ): self
    {
        $attributes = array_merge([
            'middleware'      => [],
            'validator'       => [],
            'errorMiddleware' => null,
            'prefix'          => '',
            'namespace'       => '',
        ],
            $attributes);

        $this->setMiddleware($attributes[ 'middleware' ]);
        $this->setValidator($attributes[ 'validator' ], $attributes[ 'errorMiddleware' ]);
        $this->setPrefix($attributes[ 'prefix' ]);
        $this->setNamespace($attributes[ 'namespace' ]);

        return $this;
    }

    /**
     * @param array $middlewares
     * @return RouteAttributes
     */
    public function setMiddleware( array $middlewares ): self
    {
        foreach ( $middlewares as $middleware ) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    /**
     * @param array                         $validators
     * @param ErrorMiddlewareInterface|null $errorMiddleware
     * @return RouteAttributes
     */
    public function setValidator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->errorMiddleware = $errorMiddleware;

        foreach ( $validators as $key => $validator ) {
            $this->validators[$key] = $validator;
        }

        return $this;
    }
}
