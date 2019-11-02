<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate PipelineStage.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
trait TraitPipelineStage
{
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return AddFields
	 */
	public function AddFields( string $field, $expression ): AddFields
	{
		return $this->pipeStage( 'addFields' )
		            ->set( $field, $expression );
	}
	
	/**
	 * @param       $expression
	 * @param array $boundaries
	 *
	 * @return Bucket
	 */
	public function bucket( $expression, array $boundaries ): Bucket
	{
		return $this->pipeStage( 'bucket' )
		            ->groupBy( $expression )
		            ->boundaries( $boundaries );
	}
	
	/**
	 * @param     $expression
	 * @param int $buckets
	 *
	 * @return BucketAuto
	 */
	public function bucketAuto( $expression, int $buckets ): BucketAuto
	{
		return $this->pipeStage( 'bucketAuto' )
		            ->groupBy( $expression )
		            ->buckets( $buckets );
	}
	
	/**
	 * @param string $field
	 *
	 * @return FacetPipelineStage
	 */
	public function facet( string $field ): FacetPipelineStage
	{
		return $this->pipeStage( 'facet' )
		            ->facetPipelineStage( $field );
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function count( string $field ): self
	{
		$this->pipeStage( 'count' )
		     ->set( $field );
		
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
		return $this->pipeStage( 'geoNear' )
		            ->near( $lon, $lat )
		            ->distanceField( $distanceField )
		            ->maxDistance( $distance );
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
		return $this->pipeStage( 'graphLookup' )
		            ->from( $foreignCollection )
		            ->connectFromField( $foreignField )
		            ->connectToField( $localField )
		            ->as( 'lookup' );
	}
	
	/**
	 * @param null $expression
	 *
	 * @return Group
	 */
	public function group( $expression ): Group
	{
		return $this->pipeStage( 'group' )
		            ->id( $expression );
	}
	
	/**
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function limit( int $limit ): self
	{
		$this->pipeStage( 'limit' )
		     ->set( $limit );
		
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
		return $this->pipeStage( 'lookup' )
		            ->from( $foreignCollection )
		            ->foreignField( $foreignField )
		            ->localField( $localField )
		            ->as( 'lookup' );
	}
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return Match
	 */
	public function where( string $field, string $operator, $value = null, $value2 = null ): Match
	{
		return $this->pipeStage( 'match' )
		            ->where( $field, $operator, $value, $value2 );
	}
	
	/**
	 * @param string $collection
	 * @param string $on
	 *
	 * @return $this
	 */
	public function mergeTo( string $collection, string $on = '_id' ): self
	{
		$this->pipeStage( 'merge' )
		     ->into( $collection )
		     ->on( $on );
		
		return $this;
	}
	
	/**
	 * @param string $collection
	 *
	 * @return $this
	 */
	public function saveTo( string $collection ): self
	{
		$this->pipeStage( 'out' )
		     ->set( $collection );
		
		return $this;
	}
	
	/**
	 * @param array $with
	 *
	 * @return $this
	 */
	public function with( array $with ): self
	{
		$stage = $this->pipeStage( 'project' );
		
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
		$stage = $this->pipeStage( 'project' );
		
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
		$this->pipeStage( 'replaceRoot' )
		     ->set( $expression );
		
		return $this;
	}
	
	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function sample( int $size ): self
	{
		$this->pipeStage( 'sample' )
		     ->set( $size );
		
		return $this;
	}
	
	/**
	 * @param int $skip
	 *
	 * @return $this
	 */
	public function skip( int $skip ): self
	{
		$this->pipeStage( 'skip' )
		     ->set( $skip );
		
		return $this;
	}
	
	/**
	 * @param string $field
	 * @param int    $sort
	 *
	 * @return Sort
	 */
	public function sort( string $field, int $sort = SORT_ASC ): Sort
	{
		$stage = $this->pipeStage( 'sort' );
		
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
		$this->pipeStage( 'sortByCount' )
		     ->set( $expression );
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function unwind( string $field ): self
	{
		$this->pipeStage( 'unwind' )
		     ->set( $field );
		
		return $this;
	}
}