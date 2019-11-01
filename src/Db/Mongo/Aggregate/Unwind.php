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
Class Unwind extends Stage
{
	/**
	 * @param string $field
	 */
	public function set( string $field )
	{
		$this->pipe = '$' . $field;
	}
}