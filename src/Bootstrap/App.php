<?php namespace Chukdo\Bootstrap;

use \Closure;
use \Chukdo\Helper\Convert;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class AppException extends \Exception {}

/**
 * Initialisation de l'application
 *
 * @package 	bootstrap
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class App extends Service
{
    /**
     * Tableau des ecouteurs de resolution
     *
     * @var array
     */
    protected $resolving = [];

	/**
	 * Tableau des alias
	 *
	 * @var array
	 */
	protected static $aliases = [];
	
    /**
     * Constructeur
     * Initialise l'objet
     * 
     * @return void
     */ 
    public function __construct()
    {
		$this->instance('\Chukdo\Bootstrap\App', $this);
    }

    /**
     * @param $data
     */
    public static function printr($data)
    {
        echo Convert::toHtml($data);
    }

    /**
     * @param $data
     */
    public static function printc($data)
    {
        echo Convert::toPrint($data);
    }

    /**
     * @param string $name
     * @param string $alias
     */
	public function setAlias(string $name, string $alias): void
	{
		self::$aliases[$name] = $alias;
	}

    /**
     * @param string $name
     * @return string
     */
	public function getAlias(string $name): string
	{
		return isset(self::$aliases[$name]) ? 
			self::$aliases[$name] : 
			$name;
	}

    /**
     * @param string $name
     * @return mixed|object|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
	public function make(string $name)
	{
        $alias      = $this->getAlias($name);
        $bindObject = parent::make($alias);

        $this->resolve($alias, $bindObject);

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
    public function register(string $name): void
    {
        $instance = new $name($this);
        $instance->register();
    }

    /**
     * Ecoute la resolution de tous les objets
     *
     * @param Closure $closure
     */
    public function resolvingAny(Closure $closure): void
    {
        $this->resolving['__ANY__'] = $closure;
    }

    /**
     * Ecoute la resolution d'un objet
     *
     * @param string $name
     * @param Closure $closure
     */
    public function resolving(string $name, Closure $closure): void
    {
        $this->resolving[$name] = $closure;
    }

    /**
     * @param string $name
     * @param $bindObject
     */
    protected function resolve(string $name, $bindObject)
    {
        if (isset($this->resolving['__ANY__'])) {
            $this->resolving['__ANY__']($bindObject, $name);
        }

        if (isset($this->resolving[$name])) {
            $this->resolving[$name]($bindObject, $name);
        }
    }
}