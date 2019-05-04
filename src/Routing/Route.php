<?php

namespace Chukdo\Routing;

use Chukdo\Helper\Str;
use Chukdo\Http\Request;
use Chukdo\Http\Response;
use Chukdo\Http\Url;
use Chukdo\Json\Input;

;

use Chukdo\Middleware\AppMiddleware;
use Chukdo\Middleware\Dispatcher;
use Closure;

/**
 * Gestion d'une Route.
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
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Closure
     */
    protected $closure;

    /**
     * @var array
     */
    protected $wheres = [];


    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @var ?string
     */
    protected $name = null;

    /**
     * Route constructor.
     * @param string  $method
     * @param string  $uri
     * @param Request $request
     * @param Closure $closure
     */
    public function __construct( string $method, string $uri, Request $request, Closure $closure )
    {
        $this->method  = $method;
        $this->uri     = new Url($uri);
        $this->closure = $closure;
        $this->request = $request;
    }

    /**
     * @param Input    $input
     * @param Response $response
     * @return Route
     */
    public function invoke( Input $input, Response $response ): self
    {
        ($this->closure)($input, $response);

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
        if( $this->matchMethod() ) {
            if( $this->matchDomain() ) {
                if( $this->matchSubDomain() ) {
                    if( $this->matchPath() ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function matchMethod(): bool
    {
        $method = $this->request->method();

        if( $this->method == $method || $this->method == 'ALL' ) {
            return true;
        }

        return false;
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

        if( $requestDomain == $routeDomain || $routeDomain == null ) {
            return true;
        }

        return $this->matchPattern($routeDomain, $requestDomain);
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

        if( $requestSubDomain == $routeSubDomain || $routeSubDomain == null ) {
            return true;
        }

        return $this->matchPattern($routeSubDomain, $requestSubDomain);
    }

    /**
     * @return bool
     */
    protected function matchPath(): bool
    {
        $requestPath = $this->request->url()
            ->getPath();
        $routePath   = $this->uri()
            ->getPath();

        if( $requestPath == $routePath ) {
            return true;
        }

        return $this->matchPattern($routePath, $requestPath);
    }

    /**
     * @return Url
     */
    public function uri(): Url
    {
        return $this->uri;
    }

    /**
     * @param string $routePattern
     * @param string $requestPattern
     * @return bool
     */
    protected function matchPattern( string $routePattern, string $requestPattern ): bool
    {
        if( Str::contain($routePattern, '{') ) {
            if( $inputs = $this->extractInputs($routePattern, $requestPattern) ) {
                $this->request->inputs()
                    ->merge($inputs, true);
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $routePath
     * @param string $requestPath
     * @return array|null
     */
    protected function extractInputs( string $routePath, string $requestPath ): ?array
    {
        $keys      = Str::matchAll('/\{([a-z0-9_]+)\}/', $routePath);
        $countKeys = count($keys);

        foreach( $keys as $key ) {
            $routePath = str_replace('{' . $key . '}', '(' . $this->parseWhere($key) . ')', $routePath);
        }

        $values      = (array) Str::match('`^' . $routePath . '$`', $requestPath);
        $countValues = count($values);

        if( $countValues > 0 && $countValues == $countKeys ) {
            $match = [];

            foreach( $keys as $k => $key ) {
                $match[ $key ] = $values[ $k ];
            }

            return $match;
        }

        return null;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function parseWhere( string $name ): string
    {
        return isset($this->wheres[ $name ])
            ? $this->wheres[ $name ]
            : '.*?';
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return Route
     */
    public function setName( $name ): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param array $wheres
     * @return Route
     */
    public function wheres( array $wheres ): self
    {
        foreach( $wheres as $key => $regex ) {
            $this->where($key, $regex);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param string $regex
     * @return Route
     */
    public function where( string $key, string $regex ): self
    {
        $this->wheres[ $key ] = $regex;

        return $this;
    }

    /**
     * @param array $validators
     * @return Route
     */
    public function validator( array $validators ): self
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * @param Response $response
     * @return Response
     */
    public function dispatcher( Response $response ): Response
    {
        $dispatcher = new Dispatcher($this->request, $response);
        $app        = new AppMiddleware($this->closure);

        $app->validator($this->validators);
        $dispatcher->pipe($app);

        foreach( $this->middlewares as $middleware ) {
            $dispatcher->pipe(new $middleware());
        }

        return $dispatcher->handle();
    }

    /**
     * @param array $middlewares
     * @return Route
     */
    public function middlewares( array $middlewares ): self
    {
        foreach( $middlewares as $middleware ) {
            $this->middleware($middleware);
        }

        return $this;
    }

    /**
     * @param string $middleware
     * @return Route
     */
    public function middleware( string $middleware ): self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }
}
