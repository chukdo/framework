<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Arr;
use Chukdo\Db\Mongo;

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
		$projection = [];
		
		foreach ( $this->pipe as $key => $stage ) {
			if ( Arr::isArray( $stage ) ) {
				$projection[ $key ] = [];
				
				foreach ( $stage as $item ) {
					$projection[ $key ][] = $item->projection();
				}
			} else {
				$projection[ $key ] = $stage->projection();
			}
		}
		
		return $projection;
	}
	
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return Set
	 */
	public function set( string $field, $expression ): Set
	{
		$stage = $this->pipe( 'set' );
		
		$stage->set( $field, $expression );
		
		return $stage;
	}
	
	/**
	 * @param       $expression
	 * @param array $boundaries
	 *
	 * @return Bucket
	 */
	public function bucket( $expression, array $boundaries ): Bucket
	{
		$stage = new Bucket();
		
		$stage->groupBy( $expression )
		      ->boundaries( $boundaries );
		
		$this->pipe[ '$bucket' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param     $expression
	 * @param int $buckets
	 *
	 * @return BucketAuto
	 */
	public function bucketAuto( $expression, int $buckets ): BucketAuto
	{
		$stage = new BucketAuto();
		
		$stage->groupBy( $expression )
		      ->buckets( $buckets );
		
		$this->pipe[ '$bucketAuto' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param string $field
	 *
	 * @return Facet
	 */
	public function facet( string $field ): Facet
	{
		$stage = new Facet( $field );
		
		Arr::addToSet( $this->pipe, '$facet', $stage );
		
		return $stage;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function count( string $field ): self
	{
		$this->pipe[ '$count' ] = new Count( $field );
		
		return $this;
	}
	
	/**
	 * @param float  $lon
	 * @param float  $lat
	 * @param string $distanceField
	 * @param int    $distance
	 *
	 * @return GeoNear
	 */
	public function geoNear( float $lon, float $lat, string $distanceField, int $distance ): GeoNear
	{
		$stage = new GeoNear();
		
		$stage->near( $lon, $lat )
		      ->distanceField( $distanceField )
		      ->maxDistance( $distance );
		
		$this->pipe[ '$geoNear' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param string|null $foreignCollection
	 * @param string|null $foreignField
	 * @param string|null $localField
	 *
	 * @return GraphLookup
	 */
	public function graphLookup( string $foreignCollection, string $foreignField, string $localField ): GraphLookup
	{
		$stage = new GraphLookup();
		
		$stage->from( $foreignCollection )
		      ->connectFromField( $foreignField )
		      ->connectToField( $localField )
		      ->as( 'lookup' );
		
		$this->pipe[ '$graphLookup' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param null $expression
	 *
	 * @return Group
	 */
	public function group( $expression ): Group
	{
		$stage = new Group();
		
		$stage->id( $expression );
		
		$this->pipe[ '$group' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function limit( int $limit ): self
	{
		$this->pipe[ '$limit' ] = new Limit( $limit );
		
		return $this;
	}
	
	/**
	 * @param string|null $foreignCollection
	 * @param string|null $foreignField
	 * @param string|null $localField
	 *
	 * @return Lookup
	 */
	public function lookup( string $foreignCollection, string $foreignField, string $localField ): Lookup
	{
		$stage = new lookup();
		
		$stage->from( $foreignCollection )
		      ->foreignField( $foreignField )
		      ->localField( $localField )
		      ->as( 'lookup' );
		
		$this->pipe[ '$lookup' ] = $stage;
		
		return $stage;
	}
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param        $value
	 * @param null   $value2
	 *
	 * @return Match
	 */
	public function where( string $field, string $operator, $value, $value2 = null ): Match
	{
		$match = new Match();
		
		$match->where( $field, $operator, $value, $value2 );
		
		return $this->pipe[ '$match' ] = $match;
	}
	
	/**
	 * @param string $collection
	 * @param string $on
	 *
	 * @return $this
	 */
	public function mergeTo( string $collection, string $on = '_id' ): self
	{
		$stage = new Merge();
		
		if ( $collection ) {
			$stage->into( $collection );
		}
		
		if ( $on ) {
			$stage->on( $on );
		}
		
		$this->pipe[ '$merge' ] = $stage;
		
		return $this;
	}
	
	/**
	 * @param string $collection
	 *
	 * @return $this
	 */
	public function saveTo( string $collection ): self
	{
		$this->pipe[ '$out' ] = new Out( $collection );
		
		return $this;
	}
	
	/**
	 * @param array $with
	 *
	 * @return $this
	 */
	public function with( array $with ): self
	{
		$stage = $this->pipe( 'project' );
		
		foreach ( $with as $field ) {
			$stage->set( $field, true );
		}
		
		return $this;
	}
	
	/**
	 * @param array $without
	 *
	 * @return $this
	 */
	public function without( array $without ): self
	{
		$stage = $this->pipe( 'project' );
		
		foreach ( $without as $field ) {
			$stage->set( $field, false );
		}
		
		return $this;
	}
	
	/**
	 * @param $expression
	 *
	 * @return $this
	 */
	public function replaceWith( $expression ): self
	{
		$this->pipe[ '$replaceRoot' ] = new ReplaceRoot( $expression );
		
		return $this;
	}
	
	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function sample( int $size ): self
	{
		$this->pipe[ '$sample' ] = new Sample( $size );
		
		return $this;
	}
	
	/**
	 * @param int $skip
	 *
	 * @return $this
	 */
	public function skip( int $skip ): self
	{
		$this->pipe[ '$skip' ] = new Skip( $skip );
		
		return $this;
	}
	
	/**
	 * @param $pipe
	 *
	 * @return mixed
	 */
	protected function pipe( $pipe )
	{
		$key = '$' . $pipe;
		
		if ( isset( $this->pipe[ $key ] ) ) {
			return $this->pipe[ $key ];
		}
		
		$class = '\Chukdo\DB\Mongo\Aggregate\\' . ucfirst( $pipe );
		
		return $this->pipe[ $key ] = new $class;
	}
	
	/**
	 * @param string $field
	 * @param int    $sort
	 *
	 * @return Sort
	 */
	public function sort( string $field, int $sort = SORT_ASC ): Sort
	{
		$stage = $this->pipe( 'sort' );
		
		$stage->sort( $field, $sort );
		
		return $stage;
	}
	
	/**
	 * @param $expression
	 *
	 * @return $this
	 */
	public function sortByCount( $expression ): self
	{
		$this->pipe[ '$sortByCount' ] = new SortByCount( $expression );
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function unwind( string $field ): self
	{
		$this->pipe[ '$unwind' ] = new Unwind( $field );
		
		return $this;
	}
}