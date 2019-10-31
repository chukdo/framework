<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Limit.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/limit/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Limit
{
	/**
	 * @var string
	 */
	protected $pipe;
	
	/**
	 * Limit constructor.
	 *
	 * @param int $limit
	 */
	public function __construct( int $limit )
	{
		$this->pipe = $limit;
	}
	
	/**
	 * @return int
	 */
	public function projection(): int
	{
		return $this->pipe;
	}
}