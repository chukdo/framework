<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Db\Mongo\Cursor;
use Chukdo\Db\Mongo\Match;
use Chukdo\Db\Mongo\Session;
use Chukdo\Db\Mongo\Where;
use Chukdo\Json\Json;

/**
 * Mongo Aggregate Group.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Aggregate
{
    use Session;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $pipe = [];

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/addFields/
     * @param string $field
     * @param        $expression
     * @return AddFields
     */
    public function addField( string $field, $expression ): AddFields
    {
        $addFields = new AddFields($this);
        $addFields->addField($field, $expression);
        $this->pipe[] = [ '$addFields' => $addFields ];

        return $addFields;
    }

    /**
     * @param bool $allowDiskUse
     * @param bool $bypassDocumentValidation
     * @return Json
     */
    public function all( bool $allowDiskUse = false, bool $bypassDocumentValidation = false ): Json
    {
        return new Json($this->cursor([
            'allowDiskUse'             => $allowDiskUse,
            'bypassDocumentValidation' => $bypassDocumentValidation,
            'useCursor'                => true,
        ]));
    }

    /**
     * @param array $options
     * @return Cursor
     */
    public function cursor( array $options = [] ): Cursor
    {
        $options = array_merge($this->options, $options);

        return new Cursor($this->collection->collection()
            ->aggregate($this->projection(), $options));
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        $pipes = [];

        foreach ( $this->pipe as $pipe ) {
            $json        = new Json($pipe);
            $accumulator = $json->getKeyFirst();
            $expression  = $json->getFirst();

            if ( $accumulator === '$group' || $accumulator === '$addFields' ) {
                $pipes[] = [ $accumulator => $expression->projection() ];
            }
            elseif ( $accumulator === '$match' ) {
                $pipes[] = [ $accumulator => $expression->filter() ];
            }
            else {
                $pipes[] = $pipe;
            }
        }

        return $pipes;
    }

    /**
     * @param string $field
     * @return Aggregate
     */
    public function count( string $field ): self
    {
        $this->pipe[] = [ '$count' => $field ];

        return $this;
    }

    /**
     * @return Json
     */
    public function explain(): Json
    {
        return new Json(new Cursor($this->collection->collection()
            ->aggregate($this->projection(), [
                'explain'   => true,
                'useCursor' => true,
            ])));
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/graphLookup/
     * @param string $foreignCollection
     * @param string $foreignField
     * @param string $localField
     * @param string $as
     * @param int    $maxDepth
     * @return Aggregate
     */
    public function graphLookup( string $foreignCollection, string $foreignField, string $localField, string $as = 'lookup', int $maxDepth = 3 ): self
    {
        $this->pipe[] = [
            '$graphLookup' => [
                'from'             => $foreignCollection,
                'startWith'        => '$' . $foreignField,
                'connectFromField' => $foreignField,
                'connectToField'   => $localField,
                'maxDepth'         => $maxDepth,
                'as'               => $as,
            ],
        ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/group/
     * @param $expression
     * @return Group
     */
    public function group( $expression ): Group
    {
        $group        = new Group($this, $expression);
        $this->pipe[] = [ '$group' => $group ];

        return $group;
    }

    /**
     * @param int $limit
     * @return Aggregate
     */
    public function limit( int $limit ): self
    {
        $this->pipe[] = [ '$limit' => $limit ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/lookup/
     * SELECT *, {as} FROM {localCollection} WHERE {as} IN (SELECT * FROM {foreignCollection} WHERE {foreignField=localField});
     * @param string $foreignCollection
     * @param string $foreignField
     * @param string $localField
     * @param string $as
     * @return Aggregate
     */
    public function lookup( string $foreignCollection, string $foreignField, string $localField, string $as = 'lookup' ): self
    {
        $this->pipe[] = [
            '$lookup' => [
                'from'         => $foreignCollection,
                'localField'   => $localField,
                'foreignField' => $foreignField,
                'as'           => $as,
            ],
        ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/geoNear/
     * @param float      $lon
     * @param float      $lat
     * @param int        $distance (in meter)
     * @param int        $limit
     * @param string     $as
     * @param Where|null $where
     * @return Aggregate
     */
    public function near( float $lon, float $lat, int $distance, int $limit = 20, string $as = 'distance', Where $where = null ): self
    {
        $this->pipe[] = [
            '$geoNear' => [
                'near' => [
                    'type'          => 'Point',
                    'coordinates'   => [
                        $lon,
                        $lat,
                    ],
                    'distanceField' => $as,
                    'maxDistance'   => $distance,
                    'spherical'     => true,
                    'query'         => $where
                        ? $where->filter()
                        : [],
                    'num'           => $limit,
                ],
            ],
        ];

        return $this;
    }

    /**
     * Save to collection
     * @param string $collection
     * @return Aggregate
     */
    public function out( string $collection ): self
    {
        $this->pipe[] = [ '$out' => $collection ];

        return $this;
    }

    /**
     * @param array $with
     * @param array $without
     * @return Aggregate
     */
    public function project( array $with = [], array $without = [] ): self
    {
        $project = [];

        foreach ( $with as $field ) {
            $project[ $field ] = 1;
        }

        foreach ( $without as $field ) {
            $project[ $field ] = 0;
        }

        $this->pipe[] = [ '$project' => $project ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/replaceRoot/
     * @param $expression
     * @return Aggregate
     */
    public function replaceRoot( $expression ): self
    {
        $this->pipe[] = [ '$replaceRoot' => [ 'newRoot' => Expression::parseExpression($expression) ] ];

        return $this;
    }

    /**
     * @param int $size
     * @return Aggregate
     */
    public function sample( int $size ): self
    {
        $this->pipe[] = [ '$sample' => [ 'size' => $size ] ];

        return $this;
    }

    /**
     * @param int $skip
     * @return Aggregate
     */
    public function skip( int $skip ): self
    {
        $this->pipe[] = [ '$skip' => $skip ];

        return $this;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return Aggregate
     */
    public function sort( string $field, string $sort ): self
    {
        $this->pipe[] = [
            '$sort' => [
                $field => $sort === 'asc' || $sort === 'ASC'
                    ? 1
                    : -1,
            ],
        ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/unwind/
     * > { "_id" : 1, "item" : "ABC1", sizes: [ "S", "M", "L"] }
     * = db.collection.aggregate( [ { $unwind : "$sizes" } ] )
     * < { "_id" : 1, "item" : "ABC1", "sizes" : "S" }, { "_id" : 1, "item" : "ABC1", "sizes" : "M" }, { "_id" : 1, "item" : "ABC1", "sizes" : "L" }
     * @param string $path
     * @return Aggregate
     */
    public function unwind( string $path ): self
    {
        $this->pipe[] = [ '$unwind' => '$' . $path ];

        return $this;
    }

    /**
     * https://docs.mongodb.com/manual/reference/operator/aggregation/match/
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return Match
     */
    public function where( string $field, string $operator, $value, $value2 = null ): Match
    {
        $match = new Match($this, $this->collection);
        $match->where($field, $operator, $value, $value2);
        $this->pipe[] = [ '$match' => $match ];

        return $match;
    }
}