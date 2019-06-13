<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Closure;

/**
 * Manipulation de collection de données.
 * @todo a implementer : https://laravel.com/docs/5.8/collections
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Collection
{
    /**
     * @var array
     */
    protected $collection = [];

    /**
     * Collection constructor.
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
     * Applique une fonction a la collection.
     * @param Closure $closure
     * @return Collection
     */
    public function filter( Closure $closure ): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $v ) {
            $arr[ $k ] = $closure($k, $v);
        }

        return new Collection($arr);
    }

    /**
     * Applique une fonction a la collection de maniere recursive.
     * @param closure $closure
     * @return Collection
     */
    public function filterRecursive( Closure $closure ): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $v ) {
            if ( Is::arr($v) ) {
                $arr[ $k ] = (new Collection($v))->filterRecursive($closure)->values();
            } else {
                $arr[ $k ] = $closure($k, $v);
            }
        }

        return new Collection($arr);
    }

    /**
     * @return Collection
     */
    public function clean(): self
    {
        $arr = [];

        foreach ( $this->collection as $k => $v ) {
            if ( $v !== false ) {
                $arr[ $k ] = $v;
            }
        }

        return new Collection($arr);
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
     * @return Collection
     */
    public function sort( string $key, int $order = SORT_ASC ): self
    {
        $toSort = [];
        $arr    = $this->collection;

        foreach ( $arr as $k => $v ) {
            $toSort[ $k ] = $v[ $key ];
        }

        array_multisort($toSort, $order, $arr);

        return new Collection($arr);
    }

    /**
     * @return Collection
     */
    public function resetKeys(): self
    {
        return $this->reset(array_values($this->collection));
    }

    /**
     * @param array $reset
     * @return Collection
     */
    public function reset( array $reset = [] ): self
    {
        return new Collection($reset);
    }

    /**
     * @param mixed ...$offsets
     * @return Collection
     */
    public function only( ...$offsets ): self
    {
        $only = [];

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                if ( isset($this->collection[ $offset ]) ) {
                    $only[ $offset ] = $this->collection[ $offset ];
                }
            }
        }

        return new Collection($only);
    }

    /**
     * @param mixed ...$offsets
     * @return Collection
     */
    public function except( ...$offsets ): self
    {
        $except = $this->collection;

        foreach ( $offsets as $offsetList ) {
            foreach ( (array) $offsetList as $offset ) {
                if ( isset($except[ $offset ]) ) {
                    unset($except[ $offset ]);
                }
            }
        }

        return new Collection($except);
    }
}