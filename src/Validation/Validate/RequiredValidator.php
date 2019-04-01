<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate;
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
class RequiredValidator implements Validate
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
     * @param Rule $rule
     *
     * @return bool
     */
    public function validate($input, Rule $rule): bool
    {
        return $input !== null;
    }
}
