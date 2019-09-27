<?php

namespace Chukdo\Validation\Filter;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Helper\Str;

/**
 * Validate handler.
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class BoolFilter implements FilterInterface
{
	/**
	 * @return string
	 */
	public function name(): string
	{
		return 'bool';
	}

	/**
	 * @param array $attributes
	 *
	 * @return self
	 */
	public function attributes( array $attributes ): FilterInterface
	{
		return $this;
	}

	/**
	 * @param $input
	 *
	 * @return mixed
	 */
	public function filter( $input )
	{
		if ( $input === '0' ) {
			return false;
		} else if ( $input === '1' ) {
			return true;
		}

		return $input;
	}
}