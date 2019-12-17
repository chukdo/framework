<?php

namespace Chukdo\Bootstrap;

use Chukdo\Conf\Conf;
use Chukdo\Conf\Lang;
use Chukdo\Helper\To;
use Closure;
use ReflectionException;

/**
 * Initialisation de l'application.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class App extends Service
{
    /**
     * Tableau des alias.
     *
     * @var array
     */
    protected static array $aliases = [];

    /**
     * Tableau des ecouteurs de resolution.
     *
     * @var array
     */
    protected array $resolving = [];

    /**
     * @var string
     */
    protected string $channel = '';

    /**
     * Constructeur
     * Initialise l'objet.
     */
    public function __construct()
    {
        parent::__construct();
        $this->instance( App::class, $this );
        $this->instance( Conf::class, new Conf() );
        $this->instance( Lang::class, new Lang() );
    }

    /**
     * @param $data
     */
    public function dd( $data )
    {
        if ( $data === null ) {
            die( 'Null' );
        }
        die( PHP_SAPI === 'cli'
            ? To::text( $data )
            : To::html( $data, null, null, true ) );
    }

    /**
     * @return App
     */
    public function registerHandleExceptions(): self
    {
        new HandleExceptions( $this );

        return $this;
    }

    /**
     * To store in /etc/apache2/envvars => export CHUKDO=dev
     *
     * @return string|null
     */
    public function env(): ?string
    {
        return getenv( 'CHUKDO' );
    }

    /**
     * @param string|null $channel
     *
     * @return string
     */
    public function channel( string $channel = null ): string
    {
        if ( $channel !== null ) {
            $this->channel = $channel;
        }

        return $this->channel;
    }

    /**
     * @return Conf
     */
    public function conf(): Conf
    {
        return $this->getInstance( Conf::class );
    }

    /**
     * @param string $name
     * @param bool   $bindInstance
     *
     * @return mixed|object|null
     * @throws ServiceException
     * @throws ReflectionException
     */
    public function make( string $name, bool $bindInstance = false )
    {
        $alias      = $this->getAlias( $name );
        $bindObject = parent::make( $alias );
        $this->resolve( $alias, $bindObject );
        if ( $bindInstance === true ) {
            $this->instance( $name, $bindObject );
        }

        return $bindObject;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function getAlias( string $name ): string
    {
        return self::$aliases[ $name ] ?? $name;
    }

    /**
     * @param string $name
     * @param        $bindObject
     *
     * @return App
     */
    protected function resolve( string $name, $bindObject ): self
    {
        if ( isset( $this->resolving[ '__ANY__' ] ) ) {
            $this->resolving[ '__ANY__' ]( $bindObject, $name );
        }
        if ( isset( $this->resolving[ $name ] ) ) {
            $this->resolving[ $name ]( $bindObject, $name );
        }

        return $this;
    }

    /**
     * @return Lang
     */
    public function lang(): Lang
    {
        return $this->getInstance( 'Chukdo\Conf\Lang' );
    }

    /**
     * @param string $name
     * @param string $alias
     *
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
     *
     * @return App
     */
    public function registerServices( array $services = null ): self
    {
        foreach ( $services as $service ) {
            $this->registerService( $service );
        }

        return $this;
    }

    /**
     * @param string $name
     *
     * @return App
     */
    public function registerService( string $name ): self
    {
        $instance = new $name( $this );
        $instance->register();

        return $this;
    }

    /**
     * Ecoute la resolution de tous les objets.
     *
     * @param Closure $closure
     *
     * @return App
     */
    public function resolvingAny( Closure $closure ): self
    {
        $this->resolving[ '__ANY__' ] = $closure;

        return $this;
    }

    /**
     * Ecoute la resolution d'un objet.
     *
     * @param string  $name
     * @param Closure $closure
     *
     * @return App
     */
    public function resolving( string $name, Closure $closure ): self
    {
        $this->resolving[ $name ] = $closure;

        return $this;
    }
}
