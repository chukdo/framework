<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Http\HttpException;
use Chukdo\Http\Request;

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
     * @var Request
     */
    protected $request;

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
     * @param Request $request
     */
    public function __construct( Request $request )
    {
        $this->request = $request;
        $this->reset();
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
     * @param array $middlewares
     * @return RouteAttributes
     */
    public function setMiddleware( array $middlewares ): self
    {
        foreach ( $middlewares as $middleware ) {
            if ( substr($middleware, 0, 1) == '@' ) {
                try {
                    $middleware = $this->request->conf(substr($middleware, 1));
                } catch ( \Throwable $e ) {
                }

            }

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
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }
}
