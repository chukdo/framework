<?php

namespace Chukdo\Bootstrap;

use Chukdo\Conf\Conf;
use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionException;

/**
 * Gestion des injections de dependance.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Service implements ArrayAccess
{
    /**
     * Tableau des liaisons.
     *
     * @var array
     */
    protected array $bindings = [];

    /**
     * Tableau des singletons.
     *
     * @var array
     */
    protected array $singletons = [];

    /**
     * Tableau des instances.
     *
     * @var array
     */
    protected array $instances = [];

    /**
     * Service constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return array
     */
    public function listBindings(): array
    {
        return array_keys( $this->bindings );
    }

    /**
     * @return array
     */
    public function listSingletons(): array
    {
        return array_keys( $this->singletons );
    }

    /**
     * @return array
     */
    public function listInstances(): array
    {
        return array_keys( $this->instances );
    }

    /**
     * Enregistre une closure en tant service partagé (singleton).
     *
     * @param string $name
     * @param        $closure
     *
     * @return bool
     */
    public function singleton( string $name, $closure ): bool
    {
        if ( is_string( $closure ) || $closure instanceof Closure || is_array( $closure ) ) {
            $this->singletons[ $this->formatNameSpace( $name ) ] = $closure;

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function formatNameSpace( string $name ): string
    {
        return trim( $name, '\\' );
    }

    /**
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists( $key ): bool
    {
        if ( isset( $this->bindings[ $key ] ) ) {
            return true;
        }

        if ( isset( $this->instances[ $key ] ) ) {
            return true;
        }

        if ( isset( $this->singletons[ $key ] ) ) {
            return true;
        }

        return false;
    }

    /**
     * @param mixed $key
     *
     * @return mixed|object|null
     * @throws ReflectionException
     * @throws ServiceException
     */
    public function offsetGet( $key )
    {
        return $this->make( $key );
    }

    /**
     * @param string $name
     *
     * @return mixed|object|null
     * @throws ReflectionException
     * @throws ServiceException
     */
    public function make( string $name )
    {
        if ( $instance = $this->getInstance( $name ) ) {
            return $instance;
        }

        if ( $singleton = $this->getSingleton( $name ) ) {
            $this->instance( $name, $closure = $this->getClosure( $name ) );

            return $closure;
        }

        return $this->getClosure( $name );
    }

    /**
     * Retourne une instance lié.
     *
     * @param string $name
     *
     * @return mixed|null
     */
    public function getInstance( string $name )
    {
        $name = $this->formatNameSpace( $name );

        return $this->instances[ $name ] ?? null;
    }

    /**
     * Retourne un singleton lié.
     *
     * @param string $name
     *
     * @return Closure|string|array|null
     */
    public function getSingleton( string $name )
    {
        $name = $this->formatNameSpace( $name );

        return $this->singletons[ $name ] ?? null;
    }

    /**
     * Enregistre un objet en tant que service.
     *
     * @param string $name
     * @param object $instance
     *
     * @return bool
     */
    public function instance( string $name, $instance ): bool
    {
        if ( is_object( $instance ) ) {
            $this->instances[ $this->formatNameSpace( $name ) ] = $instance;

            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return mixed|object
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function getClosure( string $name )
    {
        $bind = $this->getBind( $name )
            ?: $this->getSingleton( $name );

        if ( $bind ) {
            if ( $bind instanceof Closure ) {
                return $bind();
            }

            if ( is_string( $bind ) ) {
                return $this->getClosure( $bind );
            }

            if ( is_array( $bind ) && array_key_exists( 'class', $bind ) && array_key_exists( 'args', $bind ) ) {
                return $this->resolveService( $bind[ 'class' ], $bind[ 'args' ] );
            }
        }

        return $this->resolveClass( $name );
    }

    /**
     * Retourne une liaison existe.
     *
     * @param string $name
     *
     * @return Closure|string|array|null
     */
    public function getBind( string $name )
    {
        $name = $this->formatNameSpace( $name );

        return $this->bindings[ $name ] ?? null;
    }

    /**
     * @param string $class
     * @param array  $args
     *
     * @return object
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function resolveService( string $class, array $args = [] )
    {
        foreach ( $args as $key => $arg ) {
            if ( is_array( $arg ) ) {
                foreach ( $arg as $k => $v ) {
                    $args[ $key ][ $k ] = $this->resolveServiceArg( $v );
                }
            }
            else {
                $args[ $key ] = $this->resolveServiceArg( $arg );
            }
        }

        return $this->resolveClass( $class, $args );
    }

    /**
     * @param string $arg
     *
     * @return mixed|object|string|null
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function resolveServiceArg( string $arg )
    {
        $firstPart = $arg[ 0 ];
        $lastPart  = substr( $arg, 1 );
        if ( $firstPart === '&' ) {
            return $this->make( $lastPart );
        }

        if ( $firstPart === '@' ) {
            return $this->conf()
                        ->offsetGet( $lastPart );
        }

        return $arg;
    }

    /**
     * @return Conf
     */
    public function conf(): Conf
    {
        return new Conf();
    }

    /**
     * @param string $class
     * @param array  $args
     *
     * @return object
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function resolveClass( string $class, array $args = [] )
    {
        $reflector = new ReflectionClass( $class );
        /** C'est n'est pas une classe on genere une exception */
        if ( !$reflector->isInstantiable() ) {
            throw new ServiceException( sprintf( "[%s] is not a class", $class ) );
        }
        $constructor = $reflector->getConstructor();

        /** pas de constructeur donc pas de parametres à gerer */
        if ( $constructor === null ) {
            return new $class();
        }

        $args = empty( $args )
            ? $this->resolveArgs( $constructor )
            : $args;

        return $reflector->newInstanceArgs( $args );
    }

    /**
     * @param ReflectionMethod $constructor
     *
     * @return array
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function resolveArgs( ReflectionMethod $constructor ): array
    {
        $args       = [];
        $parameters = $constructor->getParameters();

        foreach ( $parameters as $parameter ) {
            $args[] = $this->resolveArg( $parameter );
        }

        return $args;
    }

    /**
     * @param ReflectionParameter $parameter
     *
     * @return mixed|object|null
     * @throws ReflectionException
     * @throws ServiceException
     */
    private function resolveArg( ReflectionParameter $parameter )
    {
        $name  = $parameter->getName();
        $class = $parameter->getClass();

        /** Le parametre est un objet on cherche à le resoudre  */
        if ( $cname = $parameter->getClass() ) {
            return $this->make( $cname->name );
        }

        /** Le parametre a une valeur par defaut que l'on injecte */
        if ( $parameter->isDefaultValueAvailable() ) {
            return $parameter->getDefaultValue();
        }

        /** On ne peut pas injecter le parametre, cela genere une exception     */
        throw new ServiceException( sprintf( "Unable to resolve [%s] on class [%s].", $name, $class ) );
    }

    /**
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet( $key, $value ): void
    {
        $this->bind( $key, $value );
    }

    /**
     * Enregistre une closure en tant service
     * La closure peut être une string qui s'auto reference dans service
     * Une closure qui sera retourné lors de l'appel
     * Un tableau (class, args) qui sera instancié lors de l'appel,
     * si un argument commence par @ alors il considere cela comme une auto reference dans service.
     *
     * @param string               $name
     * @param Closure|string|array $closure
     *
     * @return bool
     */
    public function bind( string $name, $closure ): bool
    {
        if ( is_string( $closure ) || $closure instanceof Closure || is_array( $closure ) ) {
            $this->bindings[ $this->formatNameSpace( $name ) ] = $closure;

            return true;
        }

        return false;
    }

    /**
     * @param mixed $key
     */
    public function offsetUnset( $key )
    {
        unset( $this->bindings[ $key ], $this->instances[ $key ], $this->singletons[ $key ] );
    }
}
