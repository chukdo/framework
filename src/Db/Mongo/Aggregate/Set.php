<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate AddFields.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Set
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @param string $field
	 * @param        $expression
	 *
	 * @return Set
	 */
	public function set( string $field, $expression ): self
	{
		$this->pipe[ $field ] = Expression::parseExpression( $expression );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return [ '$addFields' => $this->pipe ];
	}
}