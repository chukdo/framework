<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate GeoNear.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/geoNear/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class GeoNear
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @param float $lon
	 * @param float $lat
	 *
	 * @return $this
	 */
	public function near( float $lon, float $lat ): self
	{
		$this->pipe[ 'near' ] = [
			'type'        => 'Point',
			'coordinates' => [
				$lon,
				$lat,
			],
		];
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function distanceField( string $field ): self
	{
		$this->pipe[ 'distanceField' ] = $field;
		
		return $this;
	}
	
	/**
	 * @param bool $nearSphere
	 *
	 * @return $this
	 */
	public function spherical( bool $nearSphere ): self
	{
		$this->pipe[ 'spherical' ] = $nearSphere;
		
		return $this;
	}
	
	/**
	 * @param int $distance
	 *
	 * @return $this
	 */
	public function maxDistance( int $distance ): self
	{
		$this->pipe[ 'maxDistance' ] = $distance;
		
		return $this;
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
		
		return $this->pipe[ 'query' ] = $match;
	}
	
	/**
	 * @param int $multiplier
	 *
	 * @return $this
	 */
	public function distanceMultiplier( int $multiplier ): self
	{
		$this->pipe[ 'distanceMultiplier' ] = $multiplier;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function includeLocs( string $field ): self
	{
		$this->pipe[ 'includeLocs' ] = $field;
		
		return $this;
	}
	
	/**
	 * @param bool $unique
	 *
	 * @return $this
	 */
	public function uniqueDocs( bool $unique ): self
	{
		$this->pipe[ 'uniqueDocs' ] = $unique;
		
		return $this;
	}
	
	/**
	 * @param int $distance
	 *
	 * @return $this
	 */
	public function minDistance( int $distance ): self
	{
		$this->pipe[ 'minDistance' ] = $distance;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function key( string $field ): self
	{
		$this->pipe[ 'key' ] = $field;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->pipe;
	}
}