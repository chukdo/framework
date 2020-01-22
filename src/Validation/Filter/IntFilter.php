<?php

namespace Chukdo\Validation\Filter;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Helper\Str;

/**
 * Validate handler.
 *
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class IntFilter implements FilterInterface
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
        if ( Str::matchOne( '/^[0-9]+$/', $input ) ) {
            return (int) $input;
        }

        return $input;
    }
}