<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;

/**
 * Mongo Find.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Find extends Where
{
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
     * @param string ...$fields
     * @return Find
     */
    public function with( string ...$fields ): self
    {
        foreach ( $fields as $field ) {
            $this->projection[ $field ] = 1;
        }

        return $this;
    }

    /**
     * @param string ...$fields
     * @return Find
     */
    public function without( string ...$fields ): self
    {
        foreach ( $fields as $field ) {
            if ( $field == '_id' ) {
                $this->projection[ $field ] = 0;
            }
            else {
                $this->projection[ $field ] = -1;
            }
        }

        return $this;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return Find
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
     * @return Find
     */
    public function skip( int $skip ): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @return Json
     */
    public function one(): Json
    {
        foreach ( $this->limit(1)
            ->cursor() as $key => $value ) {
            return $value;
        }

        return new Json();
    }

    /**
     * @return Cursor
     */
    public function cursor(): Cursor
    {
        return new Cursor($this);
    }

    /**
     * @param int $limit
     * @return Find
     */
    public function limit( int $limit ): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return Json
     */
    public function all(): Json
    {
        $json  = new Json();

        foreach ( $this->cursor() as $key => $value ) {
            $json->offsetSet($key, $value);
        }

        return $json;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return (int) $this->collection()
            ->countDocuments($this->filter());
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection->collection();
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
}
