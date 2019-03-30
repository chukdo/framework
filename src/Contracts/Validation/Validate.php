<?php

namespace Chukdo\Contracts\Validation;

/**
 * Interface des regles de validation.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Validate
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param $input
     * @param array $param
     *
     * @return bool
     */
    public function validate($input, array $param = []): bool;
}
