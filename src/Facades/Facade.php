<?php namespace Chukdo\Facades;

use Chukdo\Bootstrap\App;

/**
 * Initialisation d'une facade
 *
 * @package    bootstrap
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Facade
{
    /**
     * Instance App
     *
     * @var \Chukdo\Bootstrap\App
     */
    static protected $app;

    /**
     * Cache facades
     *
     * @var array
     */
    static protected $facades = [];

    /**
     * @return string
     * @throws FacadeException
     */
    public static function name(): string
    {
        throw new FacadeException( "Facade does not implement 'Name' method." );
    }

    /**
     * Attache APP (extension de service) à la facade pour la resolution des injections de dependance
     *
     * @param    App $app
     *
     * @return    void
     */
    public static function setFacadeApplication( App $app ): void
    {
        static::$app = $app;
    }

    /**
     * @return App
     */
    public static function getFacadeApplication(): App
    {
        return static::$app;
    }

    /**
     * Retourne l'instance resolu attaché à un nom
     *
     * @param string $name
     *
     * @return mixed
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public static function getInstance( string $name )
    {
        if ( !isset( static::$facades[ $name ] ) ) {
            static::$facades[ $name ] = static::$app->make(
                $name,
                true
            );
        }

        return static::$facades[ $name ];
    }

    /**
     * @param string $name
     * @param string $alias
     */
    public static function setClassAlias( string $name, string $alias ): void
    {
        class_alias(
            $name,
            $alias
        );
    }

    /**
     * @return mixed
     * @throws FacadeException
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public static function object()
    {
        return static::getInstance( static::name() );
    }

    /**
     * @param string $method
     * @param array $args
     *
     * @return mixed
     * @throws FacadeException
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public static function __callStatic( string $method, array $args = [] )
    {
        $name = static::name();
        $instance = static::getInstance( $name );

        if ( !method_exists(
                $instance,
                $method
            )
            && !method_exists(
                $instance,
                '__call'
            ) ) {
            $class = get_called_class();

            throw new FacadeException( "[$class] does not implement [$method] method." );
        }

        return call_user_func_array(
            [
                $instance,
                $method
            ],
            $args
        );
    }
}