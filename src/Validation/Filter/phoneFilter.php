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
class PhoneFilter implements FilterInterface
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'phone';
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function attributes( array $attributes ): FilterInterface
    {
        return $this;
    }

    /**
     * @param $input
     * @return mixed
     */
    public function filter( $input )
    {

        if ( Str::match('/^(?:(?:\+|00)\d{2}|0)\s{0,2}[1-9](?:[\s.-]{0,3}\d{2}){4}$/', $input) ) {
            return str_replace([
                '.',
                ',',
                ' ',
                '+',
            ],
                [
                    '',
                    '',
                    '',
                    '00',
                ],
                $input);
        }

        return $input;
    }
}