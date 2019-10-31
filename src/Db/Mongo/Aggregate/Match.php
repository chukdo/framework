<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Db\Mongo\Where;

/**
 * Server Match.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Match extends Where
{
	/**
	 * @return array
	 */
	public function projection(): array
	{
		return $this->filter();
	}
}