<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\DB\Mongo\Server;

class MongoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton( Server::class, [
            'class' => Server::class,
            'args'  => [
                '@db.mongo.dsn',
                '@db.mongo.dbname',
            ],
        ] );
    }
}