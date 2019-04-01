<?php

namespace Chukdo\Bootstrap;

use Closure;

/**
 * Initialisation de l'application.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class App extends Service
{
    /**
     * Tableau des ecouteurs de resolution.
     *
     * @var array
     */
    protected $resolving = [];

    /**
     * Tableau des alias.
     *
     * @var array
     */
    protected static $aliases = [];

    /**
     * @var string
     */
    protected $env = '';

    /**
     * @var string
     */
    protected $channel = '';

    /**
     * Constructeur
     * Initialise l'objet.
     */
    public function __construct()
    {
        $this->instance(
            '\Chukdo\Bootstrap\App',
            $this
        );
    }

    public function registerHandleExceptions()
    {
        new HandleExceptions($this);
    }

    /**
     * @return bool
     */
    public function runningInConsole(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * @param string|null $env
     *
     * @return string
     */
    public function env( string $env = null ): string
    {
        if( $env != null ) {
            $this->env = $env;
        }

        return $this->env;
    }

    /**
     * @param string|null $channel
     *
     * @return string
     */
    public function channel( string $channel = null ): string
    {
        if( $channel != null ) {
            $this->channel = $channel;
        }

        return $this->channel;
    }

    /**
     * @param string $key
     *
     * @return string|null
     *
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function getConf( string $key ): ?string
    {
        return $this->make('Chukdo\Json\Conf')->offsetGet($key);
    }

    /**
     * @param string $name
     * @param string $alias
     */
    public function setAlias( string $name, string $alias ): void
    {
        self::$aliases[ $name ] = $alias;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getAlias( string $name ): string
    {
        return isset(self::$aliases[ $name ])
            ? self::$aliases[ $name ]
            : $name;
    }

    /**
     * @param string $name
     * @param bool $bindInstance
     *
     * @return mixed|object|null
     *
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function make( string $name, bool $bindInstance = false )
    {
        $alias = $this->getAlias($name);
        $bindObject = parent::make($alias);

        $this->resolve(
            $alias,
            $bindObject
        );

        if( $bindInstance == true ) {
            $this->instance(
                $name,
                $bindObject
            );
        }

        return $bindObject;
    }

    /**
     * @return App
     */
    public function getApp(): App
    {
        return $this;
    }

    /**
     * @param string $name
     */
    public function register( string $name ): void
    {
        $instance = new $name($this);
        $instance->register();
    }

    /**
     * Ecoute la resolution de tous les objets.
     *
     * @param Closure $closure
     */
    public function resolvingAny( Closure $closure ): void
    {
        $this->resolving[ '__ANY__' ] = $closure;
    }

    /**
     * Ecoute la resolution d'un objet.
     *
     * @param string $name
     * @param Closure $closure
     */
    public function resolving( string $name, Closure $closure ): void
    {
        $this->resolving[ $name ] = $closure;
    }

    /**
     * @param string $name
     * @param $bindObject
     */
    protected function resolve( string $name, $bindObject )
    {
        if( isset($this->resolving[ '__ANY__' ]) ) {
            $this->resolving[ '__ANY__' ](
                $bindObject,
                $name
            );
        }

        if( isset($this->resolving[ $name ]) ) {
            $this->resolving[ $name ](
                $bindObject,
                $name
            );
        }
    }
}
