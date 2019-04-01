<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate;
use Chukdo\Json\Input;
use Chukdo\Validation\Rule;

/**
 * Validate handler.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ArrayValidator implements Validate
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'array';
    }

    /**
     * @param $input
     * @param Rule $rule
     *
     * @return bool
     */
    public function validate($input, Rule $rule): bool
    {
        if ($input instanceof Input) {
            $param = $rule->attributes(2, 0);
            $param = array_pad($param, 2, 0);
            $min   = $param[0];
            $max   = $param[1] ?: $param[0] ?: pow(10, 9);
            $len   = $input->count();

            if ($len >= $min && $len <= $max) {
                return true;
            }
        }

        return false;
    }
}
