<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Stage.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Stage
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return [];
	}
	
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return Set
	 */
	public function set( string $field, $expression ): Set
	{
		$stage = new Set();
		$stage->set( $field, $expression );
		
		return $this->pipe[] = $stage;
	}
	
	/**
	 * @param null $expression
	 * @param null $default
	 *
	 * @return Bucket
	 */
	public function bucket( $expression = null, $default = null ): Bucket
	{
		$stage = new Bucket();
		
		if ( $expression ) {
			$stage->groupBy( $expression );
		}
		
		if ( $default ) {
			$stage->default( $default );
		}
		
		return $this->pipe[] = $stage;
	}
	
	/**
	 * @param null        $expression
	 * @param int|null    $buckets
	 * @param string|null $granularity
	 *
	 * @return BucketAuto
	 */
	public function bucketAuto( $expression = null, int $buckets = null, string $granularity = null ): BucketAuto
	{
		$stage = new BucketAuto();
		
		if ( $expression ) {
			$stage->groupBy( $expression );
		}
		
		if ( $buckets ) {
			$stage->buckets( $buckets );
		}
		
		if ( $granularity ) {
			$stage->granularity( $granularity );
		}
		
		return $this->pipe[] = $stage;
	}
	
	/**
	 * @return Facet
	 */
	public function facet(): Facet
	{
		$stage = new Facet();
		
		return $this->pipe[] = $stage;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function count( string $field ): self
	{
		$this->pipe[] = [ '$count' => $field ];
		
		return $this;
	}
	
	/**
	 * https://docs.mongodb.com/manual/reference/operator/aggregation/graphLookup/
	 * @param string $foreignCollection
	 * @param string $foreignField
	 * @param string $localField
	 * @param string $as
	 * @param int    $maxDepth
	 *
	 * @return Aggregate
	 */
	public function graphLookup( string $foreignCollection, string $foreignField, string $localField,
	                             string $as = 'lookup', int $maxDepth = 3 ): self
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
	 * @param null $expression
	 *
	 * @return Group
	 */
	public function group( $expression = null ): Group
	{
		$group        = new Group( $this, $expression );
		$this->pipe[] = [ '$group' => $group ];
		
		return $group;
	}
	
	/**
	 * @param int $limit
	 *
	 * @return Aggregate
	 */
	public function limit( int $limit ): self
	{
		$this->pipe[] = [ '$limit' => $limit ];
		
		return $this;
	}
	
	/**
	 * https://docs.mongodb.com/manual/reference/operator/aggregation/lookup/
	 * SELECT *, {as} FROM {localCollection} WHERE {as} IN (SELECT * FROM {foreignCollection} WHERE
	 * {foreignField=localField});
	 *
	 * @param string $foreignCollection
	 * @param string $foreignField
	 * @param string $localField
	 * @param string $as
	 *
	 * @return Aggregate
	 */
	public function lookup( string $foreignCollection, string $foreignField, string $localField,
	                        string $as = 'lookup' ): self
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
	 *
	 * @return Aggregate
	 */
	public function near( float $lon, float $lat, int $distance, int $limit = 20, string $as = 'distance',
	                      Where $where = null ): self
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
	 *
	 * @param string $collection
	 *
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
	 *
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
	 *
	 * @return Aggregate
	 */
	public function replaceRoot( $expression ): self
	{
		$this->pipe[] = [ '$replaceRoot' => [ 'newRoot' => Expression::parseExpression( $expression ) ] ];
		
		return $this;
	}
	
	/**
	 * @param int $size
	 *
	 * @return Aggregate
	 */
	public function sample( int $size ): self
	{
		$this->pipe[] = [ '$sample' => [ 'size' => $size ] ];
		
		return $this;
	}
	
	/**
	 * @param int $skip
	 *
	 * @return Aggregate
	 */
	public function skip( int $skip ): self
	{
		$this->pipe[] = [ '$skip' => $skip ];
		
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
		$this->pipe[] = [
			'$sort' => [
				$field => $sort === SORT_ASC
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
	 * < { "_id" : 1, "item" : "ABC1", "sizes" : "S" }, { "_id" : 1, "item" : "ABC1", "sizes" : "M" }, { "_id" : 1,
	 * "item" : "ABC1", "sizes" : "L" }
	 *
	 * @param string $path
	 *
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
	 *
	 * @return Match
	 */
	public function where( string $field, string $operator, $value, $value2 = null ): Match
	{
		$match = new Match( $this, $this->collection );
		$match->where( $field, $operator, $value, $value2 );
		$this->pipe[] = [ '$match' => $match ];
		
		return $match;
	}
}