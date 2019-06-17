<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;

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
    protected $link = [];

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
     * @var bool
     */
    protected $hiddenId = false;

    /**
     * @param string      $field
     * @param array       $with
     * @param array       $without
     * @param string|null $linked
     * @return Find
     */
    public function link( string $field, array $with = [], array $without = [], string $linked = null ): self
    {
        $link = new Link($this->collection->database(), $field);

        $this->link[] = $link->withFields($with)
            ->withoutFields($without)
            ->setLinkedName($linked);

        return $this;
    }

    /**
     * @param array $with
     * @param array $without
     * @return Find
     */
    public function project( array $with = [], array $without = [] ): self
    {
        $this->withFields($with);
        $this->withFields($without);

        return $this;
    }

    /**
     * @param array $fields
     * @return Find
     */
    public function withFields( array $fields ): self
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
    public function with( string ...$fields ): self
    {
        return $this->withFields($fields);
    }

    /**
     * @param string ...$fields
     * @return Find
     */
    public function without( string ...$fields ): self
    {
        return $this->withoutFields($fields);
    }

    /**
     * @param array $fields
     * @return Find
     */
    public function withoutFields( array $fields ): self
    {
        foreach ( $fields as $field ) {
            if ( $field == '_id' ) {
                $this->hiddenId = true;
            }
            else {
                $this->projection[ $field ] = 0;
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

            /** Suppression des ID defini par without */
            if ( $this->hiddenId ) {
                $value->offsetUnset('_id');
            }

            return $value;
        }

        return new Json();
    }

    /**
     * @return Cursor
     */
    public function cursor(): Cursor
    {
        return new Cursor($this->collection()
            ->find($this->filter(), $this->projection()));
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
    public function explain(): Json
    {
        return new Cursor($this->collection()
            ->find($this->filter(), $this->projection()), [
            'explain'   => true,
            'useCursor' => true,
        ]);
    }

    /**
     * @param bool $idAsKey
     * @return Json
     */
    public function all( bool $idAsKey = false ): Json
    {
        $json = new Json();

        foreach ( $this->cursor() as $key => $value ) {
            if ( $idAsKey ) {
                $json->offsetSet($value->offsetGet('_id'), $value);
            }
            else {
                $json->offsetSet($key, $value);
            }
        }

        foreach ( $this->link as $link ) {
            $json = $link->hydrate($json);
        }

        /** Suppression des ID defini par without */
        if ( $this->hiddenId ) {
            foreach ( $json as $key => $value ) {
                $value->offsetUnset('_id');
            }
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
     * @param string $field
     * @return Json
     */
    public function distinct( string $field ): json
    {
        return new Json($this->collection()
            ->distinct($field, $this->filter()));
    }
}
