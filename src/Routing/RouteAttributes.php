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
        $this->resetAttributes();
    }

    /**
     * @return RouteAttributes
     */
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
     * @param string $key
     * @return array|ErrorMiddlewareInterface|string
     */
    public function getAttribute(string $key)
    {
        switch ($key) {
            case 'middleware' :
                return $this->middlewares;
                break;
            case 'validator' :
                return $this->validators;
                break;
            case 'errorMiddleware' :
                return $this->errorMiddleware;
                break;
            case 'prefix' :
                return $this->prefix;
                break;
            case 'namespace' :
                return $this->namespace;
                break;
            default :
                throw new HttpException('no attribute found');
        }
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
     * @return RouteAttributes
     */
    public function setAttributes( array $attributes ): self
    {
        $this->resetAttributes()
            ->addAttributes($attributes);

        return $this;
    }

    /**
     * @param array $attributes
     * @return RouteAttributes
     */
    public function addAttributes( array $attributes ): self
    {
        $attributes = array_merge([
            'middleware'      => [],
            'validator'       => [],
            'errorMiddleware' => null,
            'prefix'          => '',
            'namespace'       => '',
        ],
            $attributes);

        $this->middleware($attributes[ 'middleware' ]);
        $this->validator($attributes[ 'validator' ], $attributes[ 'errorMiddleware' ]);
        $this->prefix($attributes[ 'prefix' ]);
        $this->namespace($attributes[ 'namespace' ]);

        return $this;
    }

    /**
     * @param array $middlewares
     * @return RouteAttributes
     */
    public function middleware( array $middlewares ): self
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
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->validators      = $validators;
        $this->errorMiddleware = $errorMiddleware;

        return $this;
    }

    /**
     * @param string|null $prefix
     * @return RouteAttributes
     */
    public function prefix( ?string $prefix ): self
    {
        $prefix = trim($prefix, '/');

        if ( strlen($prefix) > 0 ) {
            $this->prefix .= '/' . $prefix;
        }

        return $this;
    }

    /**
     * @param string|null $namespace
     * @return RouteAttributes
     */
    public function namespace( ?string $namespace ): self
    {
        $namespace = trim($namespace, '/');

        if ( strlen($namespace) > 0 ) {
            $this->namespace .= '/' . $namespace;
        }

        return $this;
    }
}
