<?php

namespace Chukdo\Db\Mongo\Aggregate;
/**
 * Server Aggregate Group.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Group
{
	/**
	 * @var array
	 */
	protected $group = [];
	
	/**
	 * @var Aggregate
	 */
	protected $aggregate;
	
	/**
	 * Group constructor.
	 *
	 * @param Aggregate $aggregate
	 * @param           $expression
	 */
	public function __construct( Aggregate $aggregate, $expression )
	{
		$this->aggregate      = $aggregate;
		$this->group[ '_id' ] = Expression::parseExpression( $expression );
	}
	
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return Group
	 */
	public function calculate( string $field, $expression ): self
	{
		$this->group[ $field ] = Expression::parseExpression( $expression );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->group;
	}
	
	public function pipe(): Aggregate
	{
		return $this->aggregate;
	}
}