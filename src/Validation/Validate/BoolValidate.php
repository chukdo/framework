<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;

/**
 * Validate handler.
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class BoolValidate implements ValidateInterface
{
	/**
	 * @return string
	 */
	public function name(): string
	{
		return 'int';
	}

	/**
	 * @param array $attributes
	 *
	 * @return self
	 */
	public function attributes( array $attributes ): ValidateInterface
	{
		return $this;
	}

	/**
	 * @param $input
	 *
	 * @return bool
	 */
	public function validate( $input ): bool
	{
		if ( is_bool( $input ) ) {
			return true;
		}

		return false;
	}
}
