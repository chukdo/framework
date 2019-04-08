<?php

namespace Chukdo\Routing;

use Chukdo\Http\Request;
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
     * @var ?string
     */
    protected $domain = null;

    /**
     * @var ?string
     */
    protected $subDomain = null;

    /**
     * Route constructor.
     * @param string  $method
     * @param string  $uri
     * @param Closure $closure
     * @param Request $request
     */
    public function __construct( string $method, string $uri, Closure $closure, Request $request )
    {
        $this->method  = $method;
        $this->uri     = $uri;
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
     * @return string
     */
    public function uri(): string
    {
        return $this->uri;
    }

    /**
     * @return bool
     */
    public function match(): bool
    {
        if( !$this->matchMethod() ) {
            return false;
        }

        if( !$this->matchDomain() ) {
            return false;
        }

        if( !$this->matchSubDomain() ) {
            return false;
        }

        // attache une route à un groupe !!!

        // subdomain > group
        // domain > group
        // method
        // uri
        //  trim(/) == ensuite !
        //  extract {} > check in wheres et replace absent par .*? puis match
        // push request > param !!!
    }

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
        $domain = $this->request->url()
            ->getDomain();

        if( $this->domain == $domain || $this->domain == null ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function matchSubDomain(): bool
    {
        $subDomain = $this->request->url()
            ->getSubDomain();

        if( $this->subDomain == $subDomain || $this->subDomain == null ) {
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
     * @param $domain
     * @return Route
     */
    public function setDomain( $domain ): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * @param $subDomain
     * @return Route
     */
    public function setSubDomain( $subDomain ): self
    {
        $this->subDomain = $subDomain;

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
