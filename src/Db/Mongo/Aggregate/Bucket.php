<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Arr;

/**
 * Aggregate Bucket.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/bucket/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Bucket
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @param $expression
	 *
	 * @return $this
	 */
	public function groupBy( $expression ): self
	{
		$this->pipe[ 'groupBy' ] = Expression::parseExpression( $expression );
		
		return $this;
	}
	
	/**
	 * @param mixed ...$bounds
	 *
	 * @return $this
	 */
	public function boundaries( ...$bounds ): self
	{
		$this->pipe[ 'boundaries' ] = Arr::spreadArgs( $bounds );
		
		return $this;
	}
	
	/**
	 * @param $value
	 *
	 * @return $this
	 */
	public function default( $value ): self
	{
		$this->pipe[ 'default' ] = $value;
		
		return $this;
	}
	
	/**
	 * @param string $name
	 * @param        $expression
	 *
	 * @return $this
	 */
	public function output( string $name, $expression ): self
	{
		Arr::addToSet( $this->pipe, 'output', Expression::parseExpression( $expression ) );
		
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