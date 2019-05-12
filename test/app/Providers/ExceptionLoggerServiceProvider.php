<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class ExceptionLoggerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('ExceptionLogger', [
            'class' => \Chukdo\Logger\Logger::class,
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
        ]);

        $this->setClassAlias(\Chukdo\Facades\ExceptionLogger::class, 'ExceptionLogger');
    }
}
