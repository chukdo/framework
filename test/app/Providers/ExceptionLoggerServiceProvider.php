<?php namespace App\Providers;

Use \Chukdo\Bootstrap\ServiceProvider;

class ExceptionLoggerServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('LoggerHandlerElastic', [
            'class' => '\Chukdo\Logger\Handlers\ElasticHandler',
            'args' => [
                '#db/elastic/host'
            ]
        ]);

        $this->app->singleton('ExceptionLogger', [
            'class' => '\Chukdo\Logger\Logger',
            'args'  => [
                'exception_' . $this->app->channel() . '_' . $this->app->env(),
                [
                    '@LoggerHandlerElastic'
                ],[
                    '@\Chukdo\Logger\Processors\RequestProcessor',
                    '@\Chukdo\Logger\Processors\BacktraceProcessor'
                ]
            ]
        ]);

        $this->setClassAlias('\Chukdo\Facades\ExceptionLogger', 'ExceptionLogger');
        $this->app->setAlias('\Chukdo\Logger\Logger', 'ExceptionLogger');
    }
}