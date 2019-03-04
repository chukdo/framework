<?php namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class LoggerHandlerServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton( 'LoggerHandler', [
            'class' => \Chukdo\Logger\Handlers\ElasticHandler::class,
            'args' => [
                '#db.elastic.host'
            ]
        ] );
    }
}