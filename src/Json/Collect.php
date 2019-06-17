<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Closure;

/**
 * Manipulation de collection de données.
 * @todo         a implementer : https://laravel.com/docs/5.8/collections
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Collect
{
    /**
     * @var Json
     */
    protected $collection;

    /**
     * Collect constructor.
     * @param $json
     */
    public function __construct( $json )
    {
        if ( $json instanceof JsonInterface ) {
            $this->collection = $json;
        }
        elseif ( Is::arr($json) ) {
            $this->collection = new Json($json);
        }
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $fieldValue
     * @param null   $fieldValue2
     * @return Collect
     */
    public function match( string $field, string $operator, $fieldValue, $fieldValue2 = null ): Collect
    {
        $json    = new json();
        $closure = $this->whereClosure($operator);

        foreach ( $this->collection as $k => $v ) {
            if ( $g = $v->get($field) ) {
                if ( $c = $closure($g, $v->get($fieldValue), $v->get($fieldValue2)) ) {
                    $json->offsetSet($k, $v);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * @param string|Closure $operator
     * @return Closure
     */
    protected function whereClosure( $operator ): Closure
    {
        $closure = null;

        switch ( $operator ) {
            case '=' :
                $closure = function( $v, $value )
                {
                    return $v == $value
                        ? $v
                        : null;
                };
                break;
            case '!=' :
                $closure = function( $v, $value )
                {
                    return $v !== $value
                        ? $v
                        : null;
                };
                break;
            case '>' :
                $closure = function( $v, $value )
                {
                    return $v > $value
                        ? $v
                        : null;
                };
                break;
            case '>=':
                $closure = function( $v, $value )
                {
                    return $v >= $value
                        ? $v
                        : null;
                };
                break;
            case '<':
                $closure = function( $v, $value )
                {
                    return $v < $value
                        ? $v
                        : null;
                };
                break;
            case '<=':
                $closure = function( $v, $value )
                {
                    return $v <= $value
                        ? $v
                        : null;
                };
                break;
            case '<>' :
                $closure = function( $v, $value, $value2 )
                {
                    return $v < $value && $v > $value2
                        ? $v
                        : null;
                };
                break;
            case '<=>' :
                $closure = function( $v, $value, $value2 )
                {
                    return $v <= $value && $v >= $value2
                        ? $v
                        : null;
                };
                break;
            case 'in':
                $closure = function( $v, $value )
                {
                    return in_array($v, (array) $value)
                        ? $v
                        : null;
                };
                break;
            case '!in':
                $closure = function( $v, $value )
                {
                    return !in_array($v, (array) $value)
                        ? $v
                        : null;
                };
                break;
            case 'type':
                $closure = function( $v, $value )
                {
                    return Str::type($v) == $value
                        ? $v
                        : null;
                };
                break;
            case '%':
                $closure = function( $v, $value, $value2 )
                {
                    return $v % $value == $value2
                        ? $v
                        : null;
                };
                break;
            case 'size':
                $closure = function( $v, $value )
                {
                    return count((array) $v) == $value
                        ? $v
                        : null;
                };
                break;
            case 'exist':
                $closure = function( $v )
                {
                    return $v
                        ? $v
                        : null;
                };
                break;
            case 'regex':
                $closure = function( $v, $value, $value2 )
                {
                    return Str::match('/' . $value . '/' . ( $value2
                            ?: 'i' ), $v)
                        ? $v
                        : null;
                };
                break;
            case 'match':
                $closure = function( $v, $value )
                {
                    $valid = false;

                    foreach ( (array) $value as $valueItem ) {
                        if ( in_array($valueItem, (array) $v) ) {
                            $valid = true;
                            break;
                        }
                    }

                    return $valid
                        ? $v
                        : null;
                };
                break;
            case 'all':
                $closure = function( $v, $value )
                {
                    foreach ( (array) $value as $valueItem ) {
                        if ( !in_array($valueItem, (array) $v) ) {
                            return null;
                        }
                    }

                    return $v;
                };
                break;
            default :
                if ( $operator instanceof Closure ) {
                    $closure = $operator;
                }
                else {
                    throw new JsonException(sprintf("Unknown operator [%s]", $operator));
                }
        }

        return $closure;
    }

    /**
     * @param string $field
     * @return Collect
     */
    public function group( string $field ): Collect
    {
        $json = new json();

        foreach ( $this->collection as $k => $v ) {
            $json->offsetGetOrSet($v->get($field))
                ->append($v);
        }

        return new Collect($json);
    }

    /**
     * @param string         $field
     * @param string|Closure $operator
     * @param                $value
     * @param null           $value2
     * @return Collect
     */
    public function where( string $field, $operator, $value, $value2 = null ): Collect
    {
        $json    = new json();
        $closure = $this->whereClosure($operator);

        foreach ( $this->collection as $k => $v ) {
            if ( $g = $v->get($field) ) {
                if ( $c = $closure($g, $value, $value2) ) {
                    $json->offsetSet($k, $v);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * @param string $id
     * @return Collect
     */
    public function keyAsId( string $id ): self
    {
        $json = new Json();

        foreach ( $this->collection as $k => $row ) {
            if ( $row instanceof JsonInterface ) {
                if ( $key = $row->unset($id) ) {
                    $json->set($key, $row);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * @param array   $paths
     * @param string  $field
     * @param Closure $closure
     * @return Collect
     */
    public function addToSet( array $paths, string $field, Closure $closure ): self
    {
        $json = new Json($this->collection);

        foreach ( $json as $k => $row ) {
            if ( $row instanceof JsonInterface ) {
                $arr = [];

                foreach ( $paths as $path ) {
                    $arr[ $path ] = $row->get($path);
                }

                $row->set($field, $closure($arr));
            }
        }

        return new Collect($json);
    }

    /**
     * @param string  $field
     * @param Closure $closure
     * @return Collect
     */
    public function filterKey( string $field, Closure $closure ): self
    {
        $json = new Json($this->collection);

        foreach ( $json as $k => $row ) {
            if ( $row instanceof JsonInterface ) {
                $r = $closure($row->get($field));

                if ( $r ) {
                    $row->set($field, $r);
                }
                else {
                    $row->unset($field);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * Applique une fonction a la collection.
     * @param Closure $closure
     * @return Collect
     */
    public function filter( Closure $closure ): self
    {
        $json = new Json();

        foreach ( $this->collection as $k => $row ) {
            if ( $row instanceof JsonInterface ) {
                $r = $row->filter($closure);

                if ( $r->count() > 0 ) {
                    $json->set($k, $r);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * Applique une fonction a la collection de maniere recursive.
     * @param closure $closure
     * @return Collect
     */
    public function filterRecursive( Closure $closure ): self
    {
        $json = new Json();

        foreach ( $this->collection as $k => $row ) {
            if ( $row instanceof JsonInterface ) {
                $r = $row->filterRecursive($closure);

                if ( $r->count() > 0 ) {
                    $json->set($k, $r);
                }
            }
        }

        return new Collect($json);
    }

    /**
     * @return Json
     */
    public function values(): Json
    {
        return $this->collection;
    }

    /**
     * @param string $key
     * @param int    $order
     * @return Collect
     */
    public function sort( string $key, int $order = SORT_ASC ): self
    {
        $toSort = [];
        $arr    = $this->collection->toArray();

        foreach ( $arr as $k => $v ) {
            $toSort[ $k ] = $v[ $key ];
        }

        array_multisort($toSort, $order, $arr);

        return new Collect($arr);
    }

    /**
     * @param mixed ...$names
     * @return Collect
     */
    public function with( ...$names ): self
    {
        $json = new Json($this->collection);
        $arr  = [];

        foreach ( $json as $k => $v ) {
            $r = $v->with($names);

            if ( $r->count() > 0 ) {
                $arr[ $k ] = $r->toArray();
            }
        }

        return new Collect($arr);
    }

    /**
     * @param mixed ...$names
     * @return Collect
     */
    public function without( ...$names ): self
    {
        $json = new Json($this->collection);
        $arr  = [];

        foreach ( $json as $k => $v ) {
            $r = $v->without($names);

            if ( $r->count() > 0 ) {
                $arr[ $k ] = $r->toArray();
            }
        }

        return new Collect($arr);
    }
}