<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate AddFields.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/addFields/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Set extends Stage
{
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
}