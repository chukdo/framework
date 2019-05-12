<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;

class DbServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('Db', [
            'class' => '\Chukdo\Db\Mongo\Mongo',
            'args'  => [
                '@db.mongo.dsn',
            ],
        ]);

        $this->setClassAlias('\Chukdo\Facades\Db', 'Db');
    }
}
