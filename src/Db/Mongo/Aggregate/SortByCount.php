<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate ReplaceRoot.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/sortByCount/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class SortByCount
{
	/**
	 * @var mixed
	 */
	protected $pipe;
	
	/**
	 * SortByCount constructor.
	 *
	 * @param $expression
	 */
	public function __construct( $expression )
	{
		$this->pipe = Expression::parseExpression( $expression );
	}
	
	/**
	 * @return array|mixed|string|null
	 */
	public function projection()
	{
		return $this->pipe;
	}
}