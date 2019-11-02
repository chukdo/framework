<?php

namespace Chukdo\Facades;

use Chukdo\Db\Mongo\Server;

/**
 * Initialisation d'une facade Db.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 * @mixin Server
 */
class Mongo extends Facade
{
    public static function name(): string
    {
        return Server::class;
    }
}
