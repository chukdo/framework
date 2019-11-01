<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Lookup.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/lookup/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Lookup extends Stage
{
	/**
	 * @param string $collection
	 *
	 * @return $this
	 */
	public function from( string $collection ): self
	{
		$this->pipe[ 'from' ] = $collection;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function localField( string $field ): self
	{
		$this->pipe[ 'localField' ] = $field;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function foreignField( string $field ): self
	{
		$this->pipe[ 'foreignField' ] = $field;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 *
	 * @return $this
	 */
	public function as( string $field ): self
	{
		$this->pipe[ 'as' ] = $field;
		
		return $this;
	}
}