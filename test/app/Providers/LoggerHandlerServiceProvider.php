<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Logger\Handlers\FileHandler;

class LoggerHandlerServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->singleton( 'LoggerHandler', [
			'class' => FileHandler::class,
			'args'  => [
				'@log.file',
			],
		] );
	}
}
