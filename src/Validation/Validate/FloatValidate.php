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
class FloatValidate implements ValidateInterface
{
    /**
     * @var int
     */
    protected int $min = 0;

    /**
     * @var int
     */
    protected int $max = 10000000;

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
        $attributes = array_pad( $attributes, 2, 0 );
        $this->min  = $attributes[ 0 ];
        $this->max  = $attributes[ 1 ]
            ?: $attributes[ 0 ]
                ?: 10000000;

        return $this;
    }

    /**
     * @param $input
     *
     * @return bool
     */
    public function validate( $input ): bool
    {
        return is_float( $input ) && $input >= $this->min && $input <= $this->max;
    }
}
