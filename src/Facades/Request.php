<?php

namespace Chukdo\Facades;

/**
 * Initialisation d'une facade Request.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 * @mixin \Chukdo\Http\Request
 */
class Request extends Facade
{
    public static function name(): string
    {
        return \Chukdo\Http\Request::class;
    }
}
