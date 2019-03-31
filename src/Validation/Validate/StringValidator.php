<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate;

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
class StringValidator implements Validate
{
    /**
     * @return string
     */
    public function name(): string
    {
        return 'string';
    }

    /**
     * @param $input
     * @param array $param
     *
     * @return bool
     */
    public function validate($input, array $param = []): bool
    {
        if (is_string($input)) {
            $param = array_pad($param, 2, 0);
            $min = $param[0];
            $max = $param[1] ?: $param[0] ?: pow(10, 9);
            $len = strlen($input);

            if ($len >= $min && $len <= $max) {
                return true;
            }
        }

        return false;
    }
}
