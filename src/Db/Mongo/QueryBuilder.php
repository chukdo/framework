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
     * QueryBuilder constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @param string ...$fields
     * @return QueryBuilder
     */
    public function select( string ...$fields ): self
    {

    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @return QueryBuilder
     */
    public function where( string $field, string $operator, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryBuilder
     */
    public function equal( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryBuilder
     */
    public function greaterThan( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryBuilder
     */
    public function greaterThanOrEqual( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryBuilder
     */
    public function lowerThan( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryBuilder
     */
    public function lowerThanOrEqual( string $field, $value ): self
    {

    }

    public function orWhere(): self
    {

    }

    /**
     * @param string $field
     * @param array  $in
     * @return QueryBuilder
     */
    public function whereIn( string $field, array $in ): self
    {

    }

    /**
     * @param string $field
     * @param array  $in
     * @return QueryBuilder
     */
    public function whereNotIn( string $field, array $in ): self
    {

    }

    public function whereBetween(): self
    {

    }

    public function whereNotBetween(): self
    {

    }

    public function whereNull(): self
    {

    }

    public function exists(): self
    {

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

    public function get()
    {

    }

    /**
     * @param string $field
     * @return int
     */
    public function increment( string $field ): int
    {

    }

    /**
     * @param string $field
     * @return int
     */
    public function decrement( string $field ): int
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
    public function max( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return QueryBuilder
     */
    public function min( array $values ): self
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