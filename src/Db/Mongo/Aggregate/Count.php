<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Count.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/count/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Count
{
	/**
	 * @var string
	 */
	protected $pipe;
	
	/**
	 * Count constructor.
	 *
	 * @param string $field
	 */
	public function __construct( string $field )
	{
		$this->pipe = $field;
	}
	
	/**
	 * @return string
	 */
	public function projection(): string
	{
		return $this->pipe;
	}
}