<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;

/**
 * Validate handler.
 *
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class UrlValidate implements ValidateInterface
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'url';
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
        if ( filter_var( $input, FILTER_VALIDATE_URL ) ) {
            return true;
        }

        return false;
    }
}
