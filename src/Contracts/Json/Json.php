<?php

namespace Chukdo\Contracts\Json;

use Chukdo\Xml\Xml;
use Closure;
use ArrayAccess;
use Countable;
use IteratorAggregate;
use Serializable;

/**
 * Interface de gestion des documents JSON.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Json extends IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /**
     * @return int
     */
    public function count(): int;

    /**
     * @param $key
     *
     * @return bool
     */
    public function offsetExists( $key ): bool;

    /**
     * @param mixed $key
     * @param null  $default
     *
     * @return mixed|null
     */
    public function offsetGet( $key, $default = null );

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return Json
     */
    public function offsetSet( $key, $value ): Json;

    /**
     * @param mixed $key
     *
     * @return mixed|null
     */
    public function offsetUnset( $key );

    /**
     * @param $key
     *
     * @return Json
     */
    public function coll( $key ): Json;

    /**
     * @return $this
     */
    public function all();

    /**
     * @return array
     */
    public function getArrayCopy(): array;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return mixed
     */
    public function getFirst();

    /**
     * @param int   $key
     * @param mixed $default
     *
     * @return mixed|null
     */
    public function getIndex( int $key = 0, $default = null );

    /**
     * @param int $key
     *
     * @return Json
     */
    public function getIndexJson( int $key = 0 ): Json;

    /**
     * @return mixed
     */
    public function getLast();

    /**
     * @return mixed|null
     */
    public function getKeyFirst();

    /**
     * @return mixed|null
     */
    public function getKeyLast();

    /**
     * @param int  $index
     * @param null $default
     *
     * @return int|mixed|string|null
     */
    public function getKeyIndex( int $index = 0, $default = null );

    /**
     * @param array $keys
     * @param null  $default
     *
     * @return mixed|null
     */
    public function offsetGetFirstInList( array $keys, $default = null );

    /**
     * @param iterable|null $merge
     * @param bool|null     $overwrite
     *
     * @return Json
     */
    public function merge( iterable $merge = null, bool $overwrite = null ): Json;

    /**
     * @param iterable|null $push
     * @param bool|null     $overwrite
     *
     * @return Json
     */
    public function push( iterable $push = null, bool $overwrite = null ): Json;

    /**
     * @param Closure $closure
     *
     * @return Json
     */
    public function filter( Closure $closure ): Json;

    /**
     * @param Closure $closure
     *
     * @return Json
     */
    public function filterRecursive( Closure $closure ): Json;

    /**
     * @param mixed ...$offsets
     *
     * @return Json
     */
    public function with( ...$offsets ): Json;

    /**
     * @param mixed ...$offsets
     *
     * @return Json
     */
    public function without( ...$offsets ): Json;

    /**
     * @param iterable|null $merge
     * @param bool|null     $overwrite
     *
     * @return Json
     */
    public function mergeRecursive( iterable $merge = null, bool $overwrite = null ): Json;

    /**
     * @return Json
     */
    public function clean(): Json;

    /**
     * @param string $path
     * @param        $value
     *
     * @return Json
     */
    public function addToSet( string $path, $value ): Json;

    /**
     * @return bool
     */
    public function isArray(): bool;

    /**
     * @param $value
     *
     * @return bool
     */
    public function in( $value ): bool;

    /**
     * @param Json $json
     *
     * @return Json
     */
    public function intersect( Json $json ): Json;

    /**
     * @param Json $json
     *
     * @return Json
     */
    public function diff( Json $json ): Json;

    /**
     * @param mixed $value
     *
     * @return Json
     */
    public function append( $value ): Json;

    /**
     * @param mixed $value
     *
     * @return Json
     */
    public function appendIfNoExist( $value ): Json;

    /**
     * @param string|null $path
     * @param null        $default
     *
     * @return mixed|null
     */
    public function get( ?string $path, $default = null );

    /**
     * @param string|null $path
     *
     * @return Json
     */
    public function getJson( ?string $path ): Json;

    /**
     * @param string $path
     * @param        $value
     *
     * @return Json
     */
    public function set( string $path, $value ): Json;

    /**
     * @param       $key
     * @param mixed $value
     *
     * @return mixed
     */
    public function offsetGetOrSet( $key, $value = null );

    /**
     * @param $value
     *
     * @return mixed
     */
    public function indexOf( $value );

    /**
     * @param string $path
     *
     * @return mixed|null
     */
    public function unset( string $path );

    /**
     * @param string $path
     *
     * @return Json
     */
    public function unwind( string $path ): Json;

    /**
     * @return Json
     */
    public function clone(): Json;

    /**
     * @param $data
     *
     * @return Json
     */
    public function reset( $data = [] ): Json;

    /**
     * @return Json
     */
    public function resetKeys(): Json;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function filled( string $path ): bool;

    /**
     * @param string $path
     *
     * @return bool
     */
    public function exists( string $path ): bool;

    /**
     * @param bool $byKey
     *
     * @return Json
     */
    public function sort( bool $byKey = false ): Json;

    /**
     * @param string $path
     * @param bool   $scalarResultOnly
     *
     * @return Json
     */
    public function wildcard( string $path, bool $scalarResultOnly = false ): Json;

    /**
     * @param string|null $prefix
     *
     * @return Json
     */
    public function toArray2D( string $prefix = null ): Json;

    /**
     * @param string|null $title
     * @param string|null $color
     *
     * @return string
     */
    public function toHtml( string $title = null, string $color = null ): string;

    /**
     * @param string|null $title
     * @param string      $color
     *
     * @return string
     */
    public function toConsole( string $title = null, string $color = '' ): string;

    /**
     * @return Xml
     */
    public function toXml(): Xml;

    /**
     * @param mixed ...$param
     *
     * @return mixed
     */
    public function to( ...$param );

    /**
     * @param mixed ...$param
     *
     * @return mixed
     */
    public function is( ...$param );

    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @param bool $prettify
     *
     * @return string
     */
    public function toJson( bool $prettify = false ): string;

    /**
     * @param string $key
     *
     * @return bool
     */
    public function __isset( string $key ): bool;

    /**
     * @param string $key
     *
     * @return mixed|null
     */
    public function __get( string $key );

    /**
     * @param string $key
     * @param        $value
     */
    public function __set( string $key, $value ): void;

    /**
     * @param string $key
     */
    public function __unset( string $key ): void;
}