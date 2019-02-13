<?php namespace App\Providers;

Use \Chukdo\Bootstrap\ServiceProvider;

class ExceptionLoggerServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('ExceptionLogger', [
            'class' => \Chukdo\Logger\Logger::class,
            'args'  => [
                'exception_' . $this->app->channel() . '_' . $this->app->env(),
                [
                    '@LoggerHandler'
                ],[
                    '@\Chukdo\Logger\Processors\RequestProcessor',
                    '@\Chukdo\Logger\Processors\BacktraceProcessor'
                ]
            ]
        ]);

        $this->setClassAlias(\Chukdo\Facades\ExceptionLogger::class, 'ExceptionLogger');
    }
}