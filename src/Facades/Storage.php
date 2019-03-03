<?php namespace Chukdo\Facades;

/**
 * Initialisation du storage
 *
 * @package 	bootstrap
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Storage extends Facade
{
	public static function name(): string
	{
		return \Chukdo\Storage\Storage::class;
	}
}