<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Unwind.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/unwind/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Unwind
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