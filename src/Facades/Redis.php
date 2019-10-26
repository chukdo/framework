<?php

namespace Chukdo\Facades;
/**
 * Initialisation d'une facade App.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 * @mixin \Chukdo\Db\Redis\Redis
 */
class Redis extends Facade
{
	public static function name(): string
	{
		return \Chukdo\Db\Redis\Redis::class;
	}
}
