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
Class Out extends Stage
{
	/**
	 * @param string $collection
	 */
	public function set( string $collection )
	{
		$this->pipe = $collection;
	}
}