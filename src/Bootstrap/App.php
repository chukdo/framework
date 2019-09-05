<?php

namespace Chukdo\Bootstrap;

use Chukdo\Helper\To;
use Closure;

/**
 * Initialisation de l'application.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class App extends Service
{
    /**
     * Tableau des alias.
     * @var array
     */
    protected static $aliases = [];
    /**
     * Tableau des ecouteurs de resolution.
     * @var array
     */
    protected $resolving = [];
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
        $this->instance('\Chukdo\Bootstrap\App', $this);
    }

    /**
     * @param $data
     */
    public function dd( $data )
    {
        die(php_sapi_name() == 'cli'
            ? To::text($data)
            : To::html($data, null, null, true));
    }

    /**
     * @return App
     */
    public function registerHandleExceptions(): self
    {
        new HandleExceptions($this);

        return $this;
    }

    /**
     * @param string|null $env
     * @return string
     */
    public function env( string $env = null ): string
    {
        if ( $env != null ) {
            $this->env = $env;
        }

        return $this->env;
    }

    /**
     * @param string|null $channel
     * @return string
     */
    public function channel( string $channel = null ): string
    {
        if ( $channel != null ) {
            $this->channel = $channel;
        }

        return $this->channel;
    }

    /**
     * @param string $key
     * @param null   $default
     * @return string|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function conf( string $key, $default = null ): ?string
    {
        return $this->make('Chukdo\Conf\Conf')
            ->offsetGet($key, $default);
    }

    /**
     * @param string $name
     * @param bool   $bindInstance
     * @return mixed|object|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function make( string $name, bool $bindInstance = false )
    {
        $alias      = $this->getAlias($name);
        $bindObject = parent::make($alias);

        $this->resolve($alias, $bindObject);

        if ( $bindInstance == true ) {
            $this->instance($name, $bindObject);
        }

        return $bindObject;
    }

    /**
     * @param string $name
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
     * @param        $bindObject
     * @return App
     */
    protected function resolve( string $name, $bindObject ): self
    {
        if ( isset($this->resolving[ '__ANY__' ]) ) {
            $this->resolving[ '__ANY__' ]($bindObject, $name);
        }

        if ( isset($this->resolving[ $name ]) ) {
            $this->resolving[ $name ]($bindObject, $name);
        }

        return $this;
    }

    /**
     * @param string $key
     * @param null   $default
     * @return string|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function lang( string $key, $default = null ): ?string
    {
        return $this->make('Chukdo\Conf\Lang')
            ->offsetGet($key, $default);
    }

    /**
     * @param string $name
     * @param string $alias
     * @return App
     */
    public function setAlias( string $name, string $alias ): self
    {
        self::$aliases[ $name ] = $alias;
        return $this;
    }

    /**
     * @return App
     */
    public function getApp(): App
    {
        return $this;
    }

    /**
     * @param array|null $services
     * @return App
     */
    public function registerServices( array $services = null ): self
    {
        foreach ( $services as $service ) {
            $this->registerService($service);
        }

        return $this;
    }

    /**
     * @param string $name
     * @return App
     */
    public function registerService( string $name ): self
    {
        $instance = new $name($this);
        $instance->register();

        return $this;
    }

    /**
     * Ecoute la resolution de tous les objets.
     * @param Closure $closure
     * @return App
     */
    public function resolvingAny( Closure $closure ): self
    {
        $this->resolving[ '__ANY__' ] = $closure;

        return $this;
    }

    /**
     * Ecoute la resolution d'un objet.
     * @param string  $name
     * @param Closure $closure
     * @return App
     */
    public function resolving( string $name, Closure $closure ): self
    {
        $this->resolving[ $name ] = $closure;

        return $this;
    }
}
