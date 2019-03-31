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
     * @param array $param
     *
     * @return bool
     */
    public function validate($input, array $param = []): bool
    {
        return is_iterable($input);
    }
}
