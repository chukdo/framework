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
     * @var array
     */
    protected $collection = [];

    /**
     * Collect constructor.
     * @param $json
     */
    public function __construct( $json )
    {
        if ( $json instanceof JsonInterface ) {
            $this->collection = $json->toArray();
        }
        elseif ( Is::arr($json) ) {
            $this->collection = $json;
        }
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return Collect
     */
    public function where( string $field, string $operator, $value, $value2 = null ): Collect
    {
        $closure = null;

        switch ( $operator ) {
            case '=' :
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v == $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '!=' :
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v != $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '>' :
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v > $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '>=':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v >= $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '<':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v < $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '<=':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v <= $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '<>' :
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v < $value && $v > $value2 ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '<=>' :
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v <= $value && $v >= $value2 ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'in':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && in_array($v, (array) $value) ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '!in':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && !in_array($v, (array) $value) ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'type':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && Str::type($v) == $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case '%':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && $v % $value == $value2
                        ?: 0 ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'size':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field && count((array) $v) == $value ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'exist':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'regex':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field
                         && Str::match('/' . $value . '/' . ( $value2
                                ?: 'i' ), $v) ) {
                        return $v;
                    }

                    return null;
                };
                break;
            case 'match':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    $valid = false;

                    if ( $k == $field ) {
                        foreach ( (array) $value as $valueItem ) {
                            if ( in_array($valueItem, (array) $v) ) {
                                $valid = true;
                                break;
                            }
                        }
                    }

                    return $valid
                        ? $v
                        : null;
                };
                break;
            case 'all':
                $closure = function( $k, $v ) use ( $field, $value, $value2 )
                {
                    if ( $k == $field ) {
                        foreach ( (array) $value as $valueItem ) {
                            if ( !in_array($valueItem, (array) $v) ) {
                                return null;
                            }
                        }
                    }

                    return $v;
                };
                break;
            default :
                throw new JsonException(sprintf("Unknown operator [%s]", $operator));

        }

        $arr = [];

        foreach ( $this->collection as $k => $row ) {
            if ( $filter = $this->closure($row, $closure) ) {
                $arr[ $k ] = $row;
            }
        }

        return new Collect($arr);
    }

    /**
     * @param array   $row
     * @param Closure $closure
     * @return array|null
     */
    protected function closure( $row, Closure $closure ): ?array
    {
        if ( !Is::arr($row) ) {
            return null;
        }

        $arr = [];

        foreach ( $row as $key => $value ) {
            if ( $r = $closure($key, $value) ) {
                $arr[ $key ] = $r;
            }
        }

        return !empty($arr)
            ? $arr
            : null;
    }

    /**
     * @param string $id
     * @return Collect
     */
    public function keyAsId( string $id ): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $row ) {
            if ( Is::arr($row) ) {
                if ( isset($row[ $id ]) ) {
                    $key = $row[ $id ];
                    unset($row[ $id ]);
                    $arr[ $key ] = $row;
                }
            }
        }

        return new Collect($arr);
    }

    /**
     * Applique une fonction a la collection.
     * @param Closure $closure
     * @return Collect
     */
    public function filter( Closure $closure ): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $row ) {
            if ( $filter = $this->closure($row, $closure) ) {
                $arr[ $k ] = $filter;
            }
        }

        return new Collect($arr);
    }

    /**
     * Applique une fonction a la collection de maniere recursive.
     * @param closure $closure
     * @return Collect
     */
    public function filterRecursive( Closure $closure ): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $v ) {
            if ( Is::arr($v) ) {
                $arr[ $k ] = ( new Collect($v) )->filterRecursive($closure)
                    ->values();
            }
            else {
                $arr[ $k ] = $closure($k, $v);
            }
        }

        return new Collect($arr);
    }

    /**
     * @return Json
     */
    public function values(): Json
    {
        return new Json($this->collection);
    }

    /**
     * @param string $key
     * @param int    $order
     * @return Collect
     */
    public function sort( string $key, int $order = SORT_ASC ): self
    {
        $toSort = [];
        $arr    = $this->collection;

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
        $arr = [];

        foreach ( $this->collection as $k => $row ) {
            if ( $filter = $this->closure($row, function( $key, $value ) use ( $names )
            {
                return in_array($key, $names)
                    ? $value
                    : null;
            }) ) {
                $arr[ $k ] = $filter;
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
        $arr = [];

        foreach ( $this->collection as $k => $row ) {
            if ( $filter = $this->closure($row, function( $key, $value ) use ( $names )
            {
                return !in_array($key, $names)
                    ? $value
                    : null;
            }) ) {
                $arr[ $k ] = $filter;
            }
        }

        return new Collect($arr);
    }
}