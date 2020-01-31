<?php

namespace Chukdo\Bootstrap;

use ReflectionClass;
use ReflectionException;

/**
 * Class loader PSR-4.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Loader
{
    /**
     * Log.
     *
     * @var array
     */
    private array $log = [];

    /**
     * Namespaces.
     *
     * @var array
     */
    private array $namespaces = [];

    /**
     * Constructeur
     * Initialise l'objet.
     */
    public function __construct()
    {
    }

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return Loader
     */
    public function register(): self
    {
        spl_autoload_register( [
                                   $this,
                                   'loadClass',
                               ] );

        return $this;
    }

    /**
     * Unregisters this instance as an autoloader.
     *
     * @return Loader
     */
    public function unregister(): self
    {
        spl_autoload_unregister( [
                                     $this,
                                     'loadClass',
                                 ] );

        return $this;
    }

    /**
     * Registers a set namespaces.
     *
     * @param array $namespaces array($namespace => $paths)
     *
     * @return Loader
     */
    public function registerNameSpaces( array $namespaces ): self
    {
        foreach ( $namespaces as $ns => $paths ) {
            $this->registerNameSpace( $ns, $paths );
        }

        return $this;
    }

    /**
     * Registers a set of PSR-4 directories for a given namespace, either
     * appending or prepending to the ones previously set for this namespace.
     *
     * @param string       $ns      The namespace
     * @param array|string $paths   The base directories
     * @param bool         $prepend Whether to prepend the directories
     *
     * @return Loader
     */
    public function registerNameSpace( string $ns, $paths, bool $prepend = false ): self
    {
        /** normalize namespace */
        $ns = trim( $ns, '\\' );
        foreach ( (array) $paths as $path ) {

            /** normalize the base directory with a separator */
            $path = rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;

            /** initialize the namespace array */
            if ( isset( $this->namespaces[ $ns ] ) === false ) {
                $this->namespaces[ $ns ] = [];
            }

            /** retain the base directory for the namespace */
            if ( $prepend ) {
                array_unshift( $this->namespaces[ $ns ], $path );
            }
            else {
                $this->namespaces[ $ns ][] = $path;
            }
        }

        return $this;
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $nsclass the fully-qualified class name
     *
     * @return bool true on success, or false on failure
     */
    public function loadClass( string $nsclass ): bool
    {
        $ns      = explode( '\\', $nsclass );
        $class   = [];
        $class[] = array_pop( $ns );

        while ( !empty( $ns ) ) {
            if ( $this->loadFile( implode( '\\', $ns ), implode( '\\', $class ) ) ) {
                return true;
            }
            array_unshift( $class, array_pop( $ns ) );
        }

        return false;
    }

    /**
     * @param string      $nsString
     * @param string|null $checkSubClass
     * @param array       $params
     *
     * @return object|null
     */
    public static function instanceClass( string $nsString, string $checkSubClass = null, array $params = [] ): ?object
    {
        static $reflector = [];

        if ( isset( $reflector[ $nsString ] ) && $reflector[ $nsString ] instanceof ReflectionClass ) {
            return $reflector[ $nsString ]->newInstanceArgs( $params );
        }

        try {
            $reflection = new ReflectionClass( $nsString );

            if ( $checkSubClass === null || ( $checkSubClass && $reflection->isSubclassOf( $checkSubClass ) ) ) {
                $reflector[ $nsString ] = $reflection;

                return $reflection->newInstanceArgs( $params );
            }
        }
        catch ( ReflectionException $e ) {
        }

        return null;
    }

    /**
     * Load the file for a namespace and class.
     *
     * @param string $ns    the namespace
     * @param string $class class name
     *
     * @return bool boolean false if no file can be loaded, or true if the file that was loaded
     */
    protected function loadFile( string $ns, string $class ): bool
    {
        if ( !isset( $this->namespaces[ $ns ] ) ) {
            return false;
        }

        foreach ( $this->namespaces[ $ns ] as $path ) {
            $file = $path . str_replace( '\\', DIRECTORY_SEPARATOR, $class ) . '.php';

            if ( $this->requireFile( $file ) ) {
                $this->log[ $class ] = $file;

                return true;
            }
        }

        return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file the file to require
     *
     * @return bool true if the file exists, false if not
     */
    protected function requireFile( string $file ): bool
    {
        if ( file_exists( $file ) ) {
            require $file;

            return true;
        }

        return false;
    }
}
