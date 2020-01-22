<?php

namespace Chukdo\Facades;

use Chukdo\Bootstrap\App;
use Chukdo\Bootstrap\ServiceException;
use ReflectionException;

/**
 * Initialisation d'une facade.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Facade
{
    /**
     * Instance App.
     *
     * @var App
     */
    protected static $app;

    /**
     * Cache facades.
     *
     * @var array
     */
    protected static $facades = [];

    /**
     * Attache APP (extension de service) à la facade pour la resolution des injections de dependance.
     *
     * @param App        $app
     * @param array|null $alias
     */
    public static function setFacadeApplication( App $app, array $alias = [] ): void
    {
        static::$app = $app;
        self::registerAlias( $alias );
    }

    /**
     * @param array $alias
     */
    public static function registerAlias( array $alias = [] ): void
    {
        foreach ( $alias as $name => $class ) {
            self::setClassAlias( $name, $class );
        }
    }

    /**
     * @param string $name
     * @param string $class
     */
    public static function setClassAlias( string $name, string $class ): void
    {
        class_alias( $class, $name );
    }

    /**
     * @return App
     */
    public static function getFacadeApplication(): App
    {
        return static::$app;
    }

    /**
     * @return mixed
     * @throws FacadeException
     * @throws ServiceException
     * @throws ReflectionException
     */
    public static function object()
    {
        return static::getInstance( static::name() );
    }

    /**
     * Retourne l'instance resolu attaché à un nom.
     *
     * @param string $name
     *
     * @return mixed
     * @throws ServiceException
     * @throws ReflectionException
     */
    public static function getInstance( string $name )
    {
        if ( !isset( static::$facades[ $name ] ) ) {
            static::$facades[ $name ] = static::$app->make( $name, true );
        }

        return static::$facades[ $name ];
    }

    /**
     * @return string
     * @throws FacadeException
     */
    public static function name(): string
    {
        throw new FacadeException( "Facade does not implement 'Name' method." );
    }

    /**
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws FacadeException
     * @throws ServiceException
     * @throws ReflectionException
     */
    public static function __callStatic( string $method, array $args = [] )
    {
        $name     = static::name();
        $instance = static::getInstance( $name );
        if ( !method_exists( $instance, $method ) && !method_exists( $instance, '__call' ) ) {
            $class = get_called_class();
            throw new FacadeException( sprintf( "[%s] does not implement [%s] method.", $class, $method ) );
        }

        return call_user_func_array( [
                                         $instance,
                                         $method,
                                     ], $args );
    }
}
