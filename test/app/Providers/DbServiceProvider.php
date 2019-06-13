<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Db\Mongo\Mongo;
use Chukdo\Facades\Db;

class DbServiceProvider extends ServiceProvider
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

        $this->setClassAlias(Db::class, 'Db');
    }
}
