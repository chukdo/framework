<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Db\Mongo\Mongo;

class MongoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Mongo::class, [
            'class' => Mongo::class,
            'args'  => [
                '@db.mongo.dsn',
                '@db.mongo.dbname',
            ],
        ]);

        $this->setClassAlias(Mongo::class, 'Mongo');
    }
}
