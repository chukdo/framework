<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Sample.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/sample/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Sample extends Stage
{
	/**
	 * @param int $size
	 */
	public function set( int $size )
	{
		$this->pipe = $size;
	}
}