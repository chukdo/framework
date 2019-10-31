<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Skip.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/skip/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Skip
{
	/**
	 * @var string
	 */
	protected $pipe;
	
	/**
	 * Sample constructor.
	 *
	 * @param int $skip
	 */
	public function __construct( int $skip )
	{
		$this->pipe = $skip;
	}
	
	/**
	 * @return int
	 */
	public function projection(): int
	{
		return $this->pipe;
	}
}