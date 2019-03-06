<?php namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate;

/**
 * Validate handler
 *
 * @package     Validation
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Required implements Validate
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'required';
    }

    /**
     * @param $input
     * @param array $param
     *
     * @return bool
     */
    public function validate( $input, array $param = [] ): bool
    {
        return $input !== null;
    }
}