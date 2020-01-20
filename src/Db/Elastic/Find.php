<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Db\Record\Link;
use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Db\Record\RecordList;
use Chukdo\Db\Record\Record;

/**
 * Server Find.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Find extends Where implements FindInterface
{
    /**
     * @var array
     */
    protected array $with = [];

    /**
     * @var array
     */
    protected array $without = [];

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var array
     */
    protected array $link = [];

    /**
     * @var array
     */
    protected array $sort = [];

    /**
     * @var int
     */
    protected int $skip = 0;

    /**
     * @var int
     */
    protected int $limit = 0;

    /**
     * @return int
     */
    public function count(): int
    {
        $count = $this->collection()
                      ->client()
                      ->count( $this->projection() );

        return (int) $count[ 'count' ];
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function projection( array $params = [] ): array
    {
        $projection = [];
        if ( $this->skip ) {
            $projection[ 'from' ] = $this->skip;
        }
        if ( $this->limit ) {
            $projection[ 'size' ] = $this->limit;
        }
        if ( $this->sort ) {
            $projection[ 'sort' ] = $this->sort;
        }

        return array_merge( $projection, $this->filter( $params ) );
    }

    /**
     * @return Record
     */
    public function one(): Record
    {
        $find = $this->search( $this->filter( [ 'size' => 1 ] ) );

        return $this->collection()
                    ->record( $this->hit( $find[ 0 ] ?? [] ) );
    }

    /**
     * @param array $filter
     *
     * @return array
     */
    protected function search( array $filter ): array
    {
        $find = $this->collection()
                     ->client()
                     ->search( $filter );

        return $find[ 'hits' ][ 'hits' ] ?? [];
    }

    /**
     * @param array $find
     *
     * @return array
     */
    protected function hit( array $find ): array
    {

        if ( !isset( $find[ '_id' ], $find[ '_source' ] ) ) {
            return [];
        }
        $id  = $find[ '_id' ];
        $hit = new Json( $find[ '_source' ] );
        foreach ( $this->without as $without ) {
            if ( $without !== '_id' ) {
                $hit->unset( $without );
            }
        }
        if ( count( $this->with ) > 0 ) {
            $filterHit = new Json();
            foreach ( $this->with as $with ) {
                $filterHit->set( $with, $hit->get( $with ) );
            }

            return $filterHit->offsetSet( '_id', $id )
                             ->toArray();
        }

        return $hit->offsetSet( '_id', $id )
                   ->toArray();
    }

    /**
     * @param bool $idAsKey
     *
     * @return RecordList
     */
    public function all( bool $idAsKey = false ): RecordList
    {
        $recordList = new RecordList( $this->collection(), $this->hits( $this->search( $this->projection() ) ), $idAsKey );
        foreach ( $this->link as $link ) {
            $recordList = $link->hydrate( $recordList );
        }

        return $recordList;
    }

    /**
     * @param array $find
     *
     * @return Json
     */
    protected function hits( array $find ): Json
    {
        $hits = new Json();
        foreach ( $find as $hit ) {
            $hits->append( $this->hit( $hit ) );
        }

        return $hits;
    }

    /**
     * @param string $field
     * @param bool   $idAsKey
     *
     * @return RecordList
     */
    public function distinct( string $field, bool $idAsKey = false ): RecordList
    {
        $recordList = new RecordList( $this->collection(), $this->hits( $this->search( $this->projection( [ 'body.aggs.' . $field . 's.terms.field' => $field, ] ) ) ), $idAsKey );
        foreach ( $this->link as $link ) {
            $recordList = $link->hydrate( $recordList );
        }

        return $recordList;
    }

    /**
     * @param string                 $field
     * @param array                  $with
     * @param array                  $without
     * @param string|null            $linked
     * @param DatabaseInterface|null $database
     *
     * @return $this
     */
    public function link( string $field, array $with = [], array $without = [], string $linked = null, DatabaseInterface $database = null ): self
    {
        $link         = new Link( $database ?? $this->collection()
                                                    ->database(), $field );
        $this->link[] = $link->with( $with )
                             ->without( $without )
                             ->setLinkedName( $linked );

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return $this
     */
    public function with( ...$fields ): self
    {
        $fields = Arr::spreadArgs( $fields );
        foreach ( $fields as $field ) {
            $this->with[] = $field;
        }

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return $this
     */
    public function without( ...$fields ): self
    {
        $fields = Arr::spreadArgs( $fields );
        foreach ( $fields as $field ) {
            $this->without[] = $field;
        }

        return $this;
    }

    /**
     * @param string $field
     * @param int    $sort
     *
     * @return $this
     */
    public function sort( string $field, int $sort = SORT_ASC ): self
    {
        $this->sort[] = $field . ':' . ( SORT_ASC
                ? 'asc'
                : 'desc' );

        return $this;
    }

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function skip( int $skip ): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function limit( int $limit ): self
    {
        $this->limit = $limit;

        return $this;
    }
}
