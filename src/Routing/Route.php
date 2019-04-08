<?php

namespace Chukdo\Routing;

use Chukdo\Helper\Str;
use Chukdo\Http\Request;
use Chukdo\Http\Url;
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
     * @return string
     */
    public function method(): string
    {
        return $this->method;
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

        //get path == request
        // match {} => replace par wheres

        // attache une route à un groupe !!!

        // method
        // uri
        //  trim(/) == ensuite !
        //  extract {} > check in wheres et replace absent par .*? puis match
        // push request > param !!!
    }

    /**
     * @return bool
     */
    public function matchPath(): bool
    {
        $requestPath = $this->request->url()
            ->getPath();
        $routePath   = $this->uri()
            ->getPath();

        if( $requestPath == $routePath ) {
            return true;
        }
        elseif( Str::contain($routePath, '{') ) {
            $params = Str::matchAll('/\{[a-z0-9_]+\}/', $routePath);

            foreach( $params as $param ) {
                $routePath = str_replace($param,
                    isset($this->wheres[ $param ])
                        ? '(' . $this->wheres[ $param ] . ')'
                        : '(.*?)',
                    $routePath);
            }

            $vars = Str::matchAll('`^' . $routePath . '$`', $requestPath);

            if( count($vars) > 0 ) {
                foreach( $params as $k => $param ) {
                    $this->request->inputs()
                        ->offsetSet(str_replace(['{', '}'], '', $param), $vars[ $k ]);
                }
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function matchMethod(): bool
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
    public function matchDomain(): bool
    {
        $requestDomain = $this->request->url()
            ->getDomain();
        $routeDomain   = $this->uri()
            ->getDomain();

        if( $requestDomain == $routeDomain || $routeDomain == null ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function matchSubDomain(): bool
    {
        $requestSubDomain = $this->request->url()
            ->getSubDomain();
        $routeSubDomain   = $this->uri()
            ->getSubDomain();

        if( $requestSubDomain == $routeSubDomain || $routeSubDomain == null ) {
            return true;
        }

        return false;
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
}
