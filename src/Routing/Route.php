<?php

namespace Chukdo\Routing;

use Chukdo\Contracts\Middleware\ErrorMiddleware as ErrorMiddlewareInterface;
use Chukdo\Contracts\Middleware\Middleware as MiddlewareInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Chukdo\Http\Url;
use Chukdo\Middleware\Dispatcher;
use Chukdo\Middleware\ValidatorMiddleware;

/**
 * Gestion d'une Route.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Route
{
    /**
     * @var string
     */
    protected string $method;

    /**
     * @var Url
     */
    protected Url $uri;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var MiddlewareInterface
     */
    protected MiddlewareInterface $appMiddleware;

    /**
     * @var array
     */
    protected array $wheres = [];

    /**
     * @var RouteAttributes
     */
    protected RouteAttributes $attributes;

    /**
     * Route constructor.
     *
     * @param string              $method
     * @param string              $uri
     * @param Request             $request
     * @param MiddlewareInterface $appMiddleware
     */
    public function __construct( string $method, string $uri, Request $request, MiddlewareInterface $appMiddleware )
    {
        $this->method        = $method;
        $this->uri           = new Url( $uri );
        $this->appMiddleware = $appMiddleware;
        $this->request       = $request;
        $this->attributes    = new RouteAttributes();
    }

    /**
     * @param array $middlewares
     *
     * @return Route
     */
    public function middleware( array $middlewares ): self
    {
        $this->attributes()
             ->setMiddleware( $middlewares );

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
     *
     * @return Route
     */
    public function validator( array $validators, ErrorMiddlewareInterface $errorMiddleware = null ): self
    {
        $this->attributes()
             ->setValidator( $validators, $errorMiddleware );

        return $this;
    }

    /**
     * @param string|null $prefix
     *
     * @return Route
     */
    public function prefix( ?string $prefix ): self
    {
        $this->attributes()
             ->setPrefix( $prefix );

        return $this;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function match(): bool
    {
        return $this->matchMethod() && $this->matchScheme() && $this->matchDomain() && $this->matchSubDomain() && $this->matchPath();
    }

    /**
     * @return bool
     */
    protected function matchMethod(): bool
    {
        $method = $this->request->method();

        return $this->method === $method || $this->method === 'ALL';
    }

    /**
     * @return bool
     */
    protected function matchScheme(): bool
    {
        $requestScheme = $this->request->url()
                                       ->getScheme();
        $routeScheme   = $this->uri()
                              ->getScheme();

        return $requestScheme === $routeScheme || $routeScheme === 'file';
    }

    /**
     * @return Url
     */
    public function uri(): Url
    {
        return $this->uri;
    }

    /**
     * @return bool
     */
    protected function matchDomain(): bool
    {
        $requestDomain = $this->request->url()
                                       ->getDomain();
        $routeDomain   = $this->uri()
                              ->getDomain();
        if ( $routeDomain === null || $requestDomain === $routeDomain ) {
            return true;
        }

        return $this->matchPattern( $routeDomain, $requestDomain );
    }

    /**
     * @param string $routePattern
     * @param string $requestPattern
     *
     * @return bool
     */
    protected function matchPattern( string $routePattern, string $requestPattern ): bool
    {
        if ( Str::contain( $routePattern, '{' ) ) {
            if ( $inputs = $this->extractInputs( $routePattern, $requestPattern ) ) {
                $this->request->inputs()
                              ->merge( $inputs, true );

                return true;
            }
        }

        return false;
    }

    /**
     * @param string $routePath
     * @param string $requestPath
     *
     * @return array|null
     */
    protected function extractInputs( string $routePath, string $requestPath ): ?array
    {
        $keys      = Str::matchAll( '/\{([a-z0-9_]+)\}/', $routePath );
        $countKeys = count( $keys );
        foreach ( $keys as $key ) {
            $routePath = str_replace( '{' . $key . '}', '(' . $this->parseWhere( $key ) . ')', $routePath );
        }
        $values      = (array)Str::match( '`^' . $routePath . '$`', $requestPath );
        $countValues = count( $values );
        if ( $countValues > 0 && $countValues === $countKeys ) {
            $match = [];
            foreach ( $keys as $k => $key ) {
                $match[ $key ] = $values[ $k ];
            }

            return $match;
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function parseWhere( string $name ): string
    {
        return $this->wheres[ $name ] ?? '.*?';
    }

    /**
     * @return bool
     */
    protected function matchSubDomain(): bool
    {
        $requestSubDomain = $this->request->url()
                                          ->getSubDomain();
        $routeSubDomain   = $this->uri()
                                 ->getSubDomain();

        if ( $routeSubDomain === null || $requestSubDomain === $routeSubDomain ) {
            return true;
        }

        return $this->matchPattern( $routeSubDomain, $requestSubDomain );
    }

    /**
     * @return bool
     */
    protected function matchPath(): bool
    {
        $requestPath = $this->request->url()
                                     ->getPath();
        $routePath   = $this->attributes()
                            ->getPrefix() . $this->uri()
                                                 ->getPath();
        if ( $requestPath === $routePath ) {
            return true;
        }

        return $this->matchPattern( $routePath, $requestPath );
    }

    /**
     * @param array $wheres
     *
     * @return Route
     */
    public function wheres( array $wheres ): self
    {
        foreach ( $wheres as $key => $regex ) {
            $this->where( $key, $regex );
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $regex
     *
     * @return Route
     */
    public function where( string $key, string $regex ): self
    {
        $this->wheres[ $key ] = $regex;

        return $this;
    }

    /**
     * @param Response $response
     *
     * @return Response
     */
    public function dispatcher( Response $response ): Response
    {
        $dispatcher = new Dispatcher( $this->request, $response );
        $dispatcher->pipe( $this->appMiddleware );

        $validators = $this->attributes()
                           ->getValidator();
        $errors     = $this->attributes()
                           ->getErrorMiddleware();

        if ( !Is::empty( $validators ) ) {
            $dispatcher->pipe( new ValidatorMiddleware( $validators, $errors ) );
        }

        foreach ( $this->attributes()
                       ->getMiddleware() as $middleware ) {
            $dispatcher->pipe( $middleware );
        }

        return $dispatcher->handle();
    }
}
