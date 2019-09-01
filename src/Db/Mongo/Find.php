<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Helper\Arr;
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
    use Session;

    /**
     * @var array
     */
    protected $projection = [];

    /**
     * @var array
     */
    protected $options = [];

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

        $this->link[] = $link->with($with)
            ->without($without)
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
        $this->with($with);
        $this->with($without);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Find
     */
    public function with( ...$fields ): self
    {
        $fields = Arr::spreadArgs($fields);

        foreach ( $fields as $field ) {
            $this->projection[ $field ] = 1;
        }

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Find
     */
    public function without( ...$fields ): self
    {
        $fields = Arr::spreadArgs($fields);

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
    public function sort( string $field, string $sort = 'ASC' ): self
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
    public function explain(): Json
    {
        $explain = $this->collection->mongo()
            ->command([
                'explain' => [
                    'find'   => $this->collection->name(),
                    'filter' => $this->filter(),
                ],
            ]);

        $json = new Json();

        $json->offsetSet('queryPlanner', $explain->get('0.queryPlanner'));
        $json->offsetSet('executionStats', $explain->get('0.executionStats'));

        return $json;
    }

    /**
     * @return Record
     */
    public function one(): Record
    {
        foreach ( $this->limit(1)
            ->cursor() as $key => $record ) {

            /** Suppression des ID defini par without */
            if ( $this->hiddenId ) {
                $record->offsetUnset('_id');
            }

            foreach ( $this->link as $link ) {
                $record = $link->hydrate($record);
            }

            return $record;
        }

        return new Record($this->collection);
    }

    /**
     * @return Cursor
     */
    public function cursor(): Cursor
    {
        $options = array_merge($this->projection(), $this->options);

        return new Cursor($this->collection, $this->collection()
            ->find($this->filter(), $options));
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
     * @param bool $idAsKey
     * @return RecordList
     */
    public function all( bool $idAsKey = false ): RecordList
    {
        $recordList = new RecordList($this->collection);

        foreach ( $this->cursor() as $key => $value ) {
            if ( $idAsKey ) {
                $recordList->offsetSet($value->offsetGet('_id'), $value);
            }
            else {
                $recordList->offsetSet($key, $value);
            }
        }

        foreach ( $this->link as $link ) {
            $recordList = $link->hydrate($recordList);
        }

        /** Suppression des ID defini par without */
        if ( $this->hiddenId ) {
            foreach ( $recordList as $key => $value ) {
                $value->offsetUnset('_id');
            }
        }

        return $recordList;
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
