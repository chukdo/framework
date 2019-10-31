<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Out.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/out/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Out
{
	/**
	 * @var string
	 */
	protected $collection;
	
	/**
	 * Count constructor.
	 *
	 * @param string $collection
	 */
	public function __construct( string $collection )
	{
		$this->collection = $collection;
	}
	
	/**
	 * @return string
	 */
	public function projection(): string
	{
		return $this->collection;
	}
}