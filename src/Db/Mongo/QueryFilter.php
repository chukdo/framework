<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\BSON\Regex;

/**
 * QueryBuilder Filter Builder.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class QueryFilter
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $filter = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * QueryFilter constructor.
     * @param Collection $collection
     * @param string     $name
     * @param null       $value
     */
    public function __construct( Collection $collection, string $name, $value = null )
    {
        $this->collection = $collection;
        $this->name       = $name;

        if ( $value ) {
            $this->eq($value);
        }
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function eq( $value ): self
    {
        $this->filter[ '$eq' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function neq( $value ): self
    {
        $this->filter[ '$ne' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function gt( $value ): self
    {
        $this->filter[ '$gt' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function gte( $value ): self
    {
        $this->filter[ '$gte' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function lt( $value ): self
    {
        $this->filter[ '$lt' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param $value
     * @return QueryFilter
     */
    public function lte( $value ): self
    {
        $this->filter[ '$lte' ] = $this->collection->closureFilterIn()($this->name, $value);

        return $this;
    }

    /**
     * @param array $in
     * @return QueryFilter
     */
    public function in( array $in ): self
    {
        $inArray = [];

        foreach ($in as $k => $v) {
            $inArray[$k] = $this->collection->closureFilterIn()($this->name, $v);
        }

        $this->filter[ '$in' ] = $inArray;

        return $this;
    }

    /**
     * @param array $nin
     * @return QueryFilter
     */
    public function nin( array $nin ): self
    {
        $ninArray = [];

        foreach ($nin as $k => $v) {
            $ninArray[$k] = $this->collection->closureFilterIn()($this->name, $v);
        }

        $this->filter[ '$nin' ] = $ninArray;

        return $this;
    }

    /**
     * @param bool $exists
     * @return QueryFilter
     */
    public function exists( bool $exists = true ): self
    {
        $this->filter[ '$exists' ] = $exists;

        return $this;
    }

    /**
     * @param string $type
     * @return QueryFilter
     */
    public function type( string $type ): self
    {
        $this->filter[ '$type' ] = $type;

        return $this;
    }

    /**
     * @param int $size
     * @return QueryFilter
     */
    public function size( int $size ): self
    {
        $this->filter[ '$size' ] = $size;

        return $this;
    }

    /**
     * @param int $divisor
     * @param int $remainder
     * @return QueryFilter
     */
    public function mod( int $divisor, int $remainder ): self
    {
        $this->filter[ '$mod' ] = [
            $divisor,
            $remainder,
        ];

        return $this;
    }

    /**
     * @param string $pattern
     * @param string $options
     * @return QueryFilter
     */
    public function regex( string $pattern, string $options = 'i' ): self
    {
        $this->filter[ '$regex' ] = new Regex($pattern, $options);

        return $this;
    }

    /**
     * @param QueryFilter ...$queryFields
     * @return QueryFilter
     */
    public function match( QueryFilter ...$queryFields ): self
    {
        if ( !isset($this->filter[ '$elemMatch' ]) ) {
            $this->filter[ '$elemMatch' ] = [];
        }

        foreach ( $queryFields as $queryField ) {
            $this->filter[ '$elemMatch' ][ $queryField->name() ] = $queryField->query();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function query(): array
    {
        return $this->filter;
    }

    /**
     * @param QueryFilter ...$queryFields
     * @return QueryFilter
     */
    public function matchAll( QueryFilter ...$queryFields ): self
    {
        if ( !isset($this->filter[ '$all' ]) ) {
            $this->filter[ '$all' ] = [];
        }

        foreach ( $queryFields as $queryField ) {
            $this->filter[ '$all' ][ $queryField->name() ] = $queryField->query();
        }

        return $this;
    }
}