<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Sort.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/sort/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Sort
{
	/**
	 * @var array
	 */
	protected $pipe = [];
	
	/**
	 * @param string $field
	 * @param int    $sort
	 *
	 * @return $this
	 */
	public function sort( string $field, int $sort = SORT_ASC ): self
	{
		$this->pipe[ $field ] = $sort === SORT_ASC
			? 1
			: -1;
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->pipe;
	}
}