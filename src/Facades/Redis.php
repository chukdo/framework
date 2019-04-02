<?php

namespace Chukdo\Facades;

/**
 * Initialisation d'une facade Redis.
 *
 * @copyright     licence MIT, Copyright (C) 2014 Domingo
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Redis extends Facade
{
    public static function name(): string {
        return \Chukdo\Db\Redis::class;
    }
}
