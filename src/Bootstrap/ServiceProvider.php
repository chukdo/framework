<?php namespace Chukdo\Bootstrap;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ServiceProviderException extends \Exception {}

/**
 * Service Provider
 *
 * @package 	bootstrap
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
abstract class ServiceProvider {

    /**
     * The application instance.
     *
     * @var \Chukdo\Bootstrap\App
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param  \Chukdo\Bootstrap\App  $app
     */
    public function __construct(\Chukdo\Bootstrap\App $app)
    {
        $this->app = $app;
    }

    /**
     * Crée un alias de classe
     *
     * @param   string  $name nom de la classe
     * @param   string  $alias alias de la classe
     * @return void
     */
    public function setClassAlias(string $name, string $alias): void
    {
        class_alias($name, $alias);
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    abstract public function register();
}