<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\Collection as MongoDbCollection;

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
     * @var MongoDbCollection
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
     * @var array
     */
    protected $projection = [];

    /**
     * @var array
     */
    protected $sort = [];

    /**
     * @var int
     */
    protected $skip = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * QueryBuilder constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection->collection();
    }

    /**
     * @param string $name
     * @return QueryField
     */
    public function and( string $name ): QueryField
    {
        return $this->and[] = $this->field($name);
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
        return $this->or[] = $this->field($name);
    }

    /**
     * @param array|string $fields
     * @return QueryBuilder
     */
    public function with( $fields ): self
    {
        $fields = (array) $fields;

        foreach ( $fields as $field ) {
            $this->projection[ $field ] = 1;
        }

        return $this;
    }

    /**
     * @param array|string $fields
     * @return QueryBuilder
     */
    public function without( $fields ): self
    {
        $fields = (array) $fields;

        foreach ( $fields as $field ) {
            $this->projection[ $field ] = -1;
        }

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function withoutId(): self
    {
        $this->projection[ '_id' ] = 0;

        return $this;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return QueryBuilder
     */
    public function sort( string $field, string $sort ): self
    {
        $this->sort[ $field ] = $sort === 'asc' || $sort === 'ASC'
            ? 1
            : -1;

        return $this;
    }

    /**
     * @param int $skip
     * @return QueryBuilder
     */
    public function skip( int $skip ): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @param int $limit
     * @return QueryBuilder
     */
    public function limit( int $limit ): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return Cursor
     */
    public function get(): Cursor
    {
        return new Cursor($this);
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function query(): array
    {
        $query = [];
        $and   = array_map(function( QueryField $query )
        {
            return [ $query->name() => $query->query() ];
        }, $this->and);


        $or = array_map(function( QueryField $query )
        {
            return [ $query->name() => $query->query() ];
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
     * @return array
     */
    public function projection(): array
    {
        $projection = [
            'projection'      => $this->projection,
            'noCursorTimeout' => false,
        ];

        if ( !empty($this->sort) ) {
            $projection[ 'sort' ] = $this->sort;
        }

        if ( $this->skip > 0 ) {
            $projection[ 'skip' ] = $this->skip;
        }

        if ( $this->limit > 0 ) {
            $projection[ 'limit' ] = $this->limit;
        }

        return $projection;
    }

    public function one()
    {
        return $this->collection()
            ->one($this->query(), $this->projection());
    }

    /**
     * @param string $field
     * @return QueryBuilder
     */
    public function groupBy( string $field ): self
    {
        return $this;
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
     * @return int
     */
    public function update(): int
    {

    }

    public function delete(): int
    {

    }
}