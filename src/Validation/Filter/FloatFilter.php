<?php

namespace Chukdo\Validation\Filter;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;

/**
 * Validate handler.
 *
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class FloatFilter implements FilterInterface
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'float';
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
        $input = (string) $input;

        if ( Str::match( '/^[0-9 .,]+$/', $input ) ) {
            $input = str_replace( ' ', '', $input );

            if ( Str::contain( $input, '.' ) && Str::contain( $input, ',' ) ) {
                $input = str_replace( '.', '', $input );
            }

            $input = str_replace( ',', '.', $input );

            return (float) $input;
        }

        return $input;
    }
}