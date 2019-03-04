<?php namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class QueueServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton( 'Queue', [
            'class' => '\Chukdo\Db\Redis',
            'args' => [
                'redis://127.0.0.1:6379'
            ]
        ] );

        $this->setClassAlias( '\Chukdo\Facades\Redis', 'Queue' );
    }
}