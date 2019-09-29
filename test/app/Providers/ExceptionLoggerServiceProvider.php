<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Logger\Logger;
use Chukdo\Facades\ExceptionLogger;

class ExceptionLoggerServiceProvider extends ServiceProvider
{
	public function register(): void
	{
		$this->app->singleton( 'ExceptionLogger', [
			'class' => Logger::class,
			'args'  => [
				'exception_' . $this->app->channel() . '_' . $this->app->env(),
				[
					'&LoggerHandler',
				],
				[
					'&\Chukdo\Logger\Processors\RequestProcessor',
					'&\Chukdo\Logger\Processors\BacktraceProcessor',
				],
			],
		] );

		$this->setClassAlias( ExceptionLogger::class, 'ExceptionLogger' );
	}
}
