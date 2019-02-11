<?php namespace Chukdo\Bootstrap;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Gestion des exceptions
 *
 * @package 	Exception
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ServiceException extends \Exception {}

/**
 * Gestion des injections de dependance
 *
 * @package 	bootstrap
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Service implements ArrayAccess
{
	/**
	 * Tableau des liaisons
	 *
	 * @var array
	 */
	protected $bindings = [];

    /**
     * Tableau des singletons
     *
     * @var array
     */
    protected $singletons = [];

	/**
	 * Tableau des instances
	 *
	 * @var array
	 */
	protected $instances = [];

    /**
     * Tableau de configuration
     *
     * @var array
     */
    protected $conf = [];

    /**
     * Service constructor.
     */
    public function __construct() {}

    /**
     * Enregistre une closure en tant service
     * La closure peut être une string qui s'auto reference dans service
     * Une closure qui sera retourné lors de l'appel
     * Un tableau (class, args) qui sera instancié lors de l'appel,
     * si un argument commence par @ alors il considere cela comme une auto reference dans service
     *
     * @param string                $name
     * @param Closure|string|array  $closure
     * @return bool
     */
	public function bind(string $name, $closure): bool
	{
		if (is_string($closure) || $closure instanceof \Closure || is_array($closure)) {
			$this->bindings[$name] = $closure;
			return true;
		}
			
		return false;
	}

    /**
     * Enregistre une closure en tant service partagé (singleton)
     *
     * @param string $name
     * @param $closure
     * @return bool
     */
	public function singleton(string $name, $closure): bool
	{
        if (is_string($closure) || $closure instanceof \Closure || is_array($closure)) {
            $this->singletons[$name] = $closure;
            return true;
        }

        return false;
	}
	
	/**
	 * Enregistre un objet en tant que service 
	 *
	 * @param	string	$name
	 * @param	object	$instance
	 * @return	bool
	 */
	public function instance(string $name, $instance): bool
	{
		if (is_object($instance)) {
			$this->instances[$name] = $instance;
			return true;
		}
		
		return false;
	}

    /**
     * @param array $conf
     * @return bool
     */
	public function conf(array $conf): bool
    {
        $this->conf = $conf;
        return true;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getConf(string $key): ?string
    {
        $key = '/' . trim($key, '/');

        if (isset($this->conf[$key])) {
            return $this->conf[$key];
        }

        return null;
    }

    /**
     * Retourne une instance lié
     *
     * @param	string $name
     * @return	object|null
     */
    public function getInstance(string $name)
    {
        return isset($this->instances[$name]) ?
            $this->instances[$name] :
            null;
    }

    /**
     * Retourne un singleton lié
     *
     * @param	string $name
     * @return	Closure|string|array|null
     */
    public function getSingleton(string $name)
    {
        return isset($this->singletons[$name]) ?
            $this->singletons[$name] :
            null;
    }

    /**
     * Retourne une liaison existe
     *
     * @param	string $name
     * @return	Closure|string|array|null
     */
    public function getBind(string $name)
    {
        return isset($this->bindings[$name]) ?
            $this->bindings[$name] :
            null;
    }

    /**
     * @param string $name
     * @return mixed|object|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function make(string $name)
    {
        if ($instance = $this->getInstance($name)) {
            return $instance;

        } else if ($singleton = $this->getSingleton($name)) {
            $this->instance($name, $closure = $this->getClosure($name));
            return $closure;
        }

        return $this->getClosure($name);
    }

    /**
     * @param string $name
     * @return mixed|object
     * @throws ServiceException
     * @throws \ReflectionException
     */
    private function getClosure(string $name)
    {
        $bind = $this->getBind($name) ?: $this->getSingleton($name);

        if ($bind) {
            if ($bind instanceof Closure) {
                return $bind();

            } elseif (is_string($bind)) {
                return $this->getClosure($bind);

            } elseif (is_array($bind)) {
                if (array_key_exists('class', $bind) && array_key_exists('args', $bind)) {
                    return $this->resolveService($bind['class'], $bind['args']);
                }
            }
        }

        return $this->resolveClass($name);
    }

    /**
     * @param string $class
     * @param array $args
     * @return object
     * @throws ServiceException
     * @throws \ReflectionException
     */
	private function resolveService(string $class, array $args = [])
	{
		foreach ($args as $key => $arg) {
		    if (is_array($arg)) {
		        foreach ($arg as $k => $v) {
                    $args[$key][$k] = $this->resolveServiceArg($v);
                }
            } else {
                $args[$key] = $this->resolveServiceArg($arg);
            }
		}

		return $this->resolveClass($class, $args);
	}

    /**
     * @param string $arg
     * @return mixed|object|string
     * @throws ServiceException
     * @throws \ReflectionException
     */
	private function resolveServiceArg(string $arg)
    {
        $firstPart  = substr($arg, 0, 1);
        $lastPart   = substr($arg, 1);

        if ($firstPart == '@') {
            return $this->getClosure($lastPart);

        } else if ($firstPart == '#') {
            return $this->getConf($lastPart);
        }

        return $arg;
    }

    /**
     * @param string $class
     * @param array $args
     * @return object
     * @throws ServiceException
     * @throws \ReflectionException
     */
	private function resolveClass(string $class, array $args = [])
	{
		$reflector	= new ReflectionClass($class);
		
		/** C'est n'est pas une classe on genere une exception */
		if (!$reflector->isInstantiable()) {
			throw new ServiceException("[$class] is not a class");
		}

		$constructor = $reflector->getConstructor();

		/** pas de constructeur donc pas de parametres à gerer */
		if (is_null($constructor)) {
			return new $class;
		}

		$args = empty($args) ? $this->resolveArgs($constructor) : $args;

		return $reflector->newInstanceArgs($args);
	}

    /**
     * @param ReflectionMethod $constructor
     * @return array
     * @throws ServiceException
     * @throws \ReflectionException
     */
	private function resolveArgs(ReflectionMethod $constructor): array
	{
		$args		= [];
		$parameters = $constructor->getParameters();	
			
		foreach ($parameters as $parameter) {
			$args[] = $this->resolveArg($parameter);
		}

		return $args;
	}

    /**
     * @param ReflectionParameter $parameter
     * @return function|mixed
     * @throws ServiceException
     * @throws \ReflectionException
     */
	private function resolveArg(ReflectionParameter $parameter)
	{
		$name	= $parameter->getName(); 
		$class 	= $parameter->getClass();
		
		/** Le parametre est un objet on cherche à le resoudre  */
		if ($cname = $parameter->getClass()) {
			return $this->getClosure($cname->name);
		
		/** Le parametre a une valeur par defaut que l'on injecte */
		} elseif ($parameter->isDefaultValueAvailable()) {
			return $parameter->getDefaultValue();
		}
		
		/** On ne peut pas injecter le parametre, cela genere une exception	 */
		throw new ServiceException("Unable to resolve [$name] on class [$class].");
	}

    /**
     * @param mixed $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->bindings[$key]) ?
            true :
            isset($this->instances[$key]) ?
                true :
                isset($this->singletons[$key]) ?
                    true :
                    false;
    }

    /**
     * @param mixed $key
     * @return mixed|object|null
     * @throws ServiceException
     * @throws \ReflectionException
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value): void
    {
        $this->bind($key, $value);
    }

    /**
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        unset(
            $this->bindings[$key],
            $this->instances[$key],
            $this->singletons[$key]
        );
    }
}