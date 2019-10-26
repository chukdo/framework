<?php

namespace Chukdo\Db\Mongo\Aggregate;
/**
 * Server Aggregate AddFields.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class AddFields
{
	/**
	 * @var array
	 */
	protected $addFields = [];
	
	/**
	 * @var Aggregate
	 */
	protected $aggregate;
	
	/**
	 * Group constructor.
	 *
	 * @param Aggregate $aggregate
	 */
	public function __construct( Aggregate $aggregate )
	{
		$this->aggregate = $aggregate;
	}
	
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return AddFields
	 */
	public function addField( string $field, $expression ): self
	{
		$this->addFields[ $field ] = Expression::parseExpression( $expression );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->addFields;
	}
	
	public function pipe(): Aggregate
	{
		return $this->aggregate;
	}
}