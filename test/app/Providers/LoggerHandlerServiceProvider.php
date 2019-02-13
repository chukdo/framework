<?php namespace App\Providers;

Use \Chukdo\Bootstrap\ServiceProvider;

class LoggerHandlerServiceProvider extends ServiceProvider {

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('LoggerHandler', [
            'class' => '\Chukdo\Logger\Handlers\ElasticHandler',
            'args' => [
                '#db.elastic.host'
            ]
        ]);
    }
}