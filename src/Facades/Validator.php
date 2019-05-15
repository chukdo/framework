<?php

namespace Chukdo\Facades;

/**
 * Initialisation d'une facade Validator.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 * @mixin \Chukdo\Validation\Validator
 */
class Validator extends Facade
{
    public static function name(): string
    {
        return \Chukdo\Validation\Validator::class;
    }
}
