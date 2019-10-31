<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate ReplaceRoot.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/replaceRoot/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class ReplaceRoot
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * ReplaceRoot constructor.
	 *
	 * @param $expression
	 */
	public function __construct( $expression )
	{
		$this->pipe = [ 'newRoot' => Expression::parseExpression( $expression ) ];
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->pipe;
	}
}