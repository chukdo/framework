<?php

Namespace Chukdo\DB\Mongo;

/**
 * QueryBuilder Builder.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class QueryBuilder
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $and = [];

    /**
     * @var array
     */
    protected $or = [];

    /**
     * QueryBuilder constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @param string $name
     * @return QueryField
     */
    public function and( string $name ): QueryField
    {
        return $this->and[$name] = $this->field($name);
    }

    /**
     * @param string $name
     * @return QueryField
     */
    public function field( string $name ): QueryField
    {
        return new QueryField($name);
    }

    /**
     * @param string $name
     * @return QueryField
     */
    public function or( string $name ): QueryField
    {
        return $this->or[$name] = $this->field($name);
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        $query = [];
        $and   = array_map(function( QueryField $query )
        {
            return $query->query();
        }, $this->and);


        $or = array_map(function( QueryField $query )
        {
            return $query->query();
        }, $this->or);

        if ( !empty($and) ) {
            $query[ '$and' ] = $and;
        }

        if ( !empty($or) ) {
            $query[ '$or' ] = $or;
        }

        return $query;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return QueryBuilder
     */
    public function orderBy( string $field, string $sort ): self
    {

    }

    /**
     * @param string $field
     * @return QueryBuilder
     */
    public function groupBy( string $field ): self
    {

    }

    /**
     * @param int $skip
     * @return QueryBuilder
     */
    public function skip( int $skip ): self
    {

    }

    /**
     * @param int $take
     * @return QueryBuilder
     */
    public function take( int $take ): self
    {

    }

    /**
     * @return int
     */
    public function count(): int
    {

    }

    /**
     * @param array $values
     * @return int
     */
    public function insert( array $values ): int
    {

    }

    /**
     * @param array $values
     * @return string
     */
    public function insertGetId( array $values ): string
    {

    }

    /**
     * @param array $values
     * @return QueryBuilder
     */
    public function set( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return QueryBuilder
     */
    public function unset( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return QueryBuilder
     */
    public function push( array $values ): self
    {

    }

    /**
     * @return int
     */
    public function update(): int
    {

    }

    public function delete(): int
    {

    }
}