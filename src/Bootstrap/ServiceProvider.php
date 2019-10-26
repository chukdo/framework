<?php

namespace Chukdo\Bootstrap;
/**
 * Service Provider.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
abstract class ServiceProvider
{
	/**
	 * @var App
	 */
	protected $app;
	
	/**
	 * ServiceProvider constructor.
	 *
	 * @param App $app
	 */
	public function __construct( App $app )
	{
		$this->app = $app;
	}
	
	/**
	 * @param string $name
	 * @param string $alias
	 */
	public function setClassAlias( string $name, string $alias ): void
	{
		class_alias( $name, $alias );
	}
	
	/**
	 * @return mixed
	 */
	abstract public function register(): void;
}
