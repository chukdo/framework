<?php

namespace App\Providers;

use Chukdo\Bootstrap\ServiceProvider;
use Chukdo\Facades\Facade;

class FacadeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Facade::setFacadeApplication($this->app);
        Facade::setClassAlias(Facade::class, 'Facade');
        Facade::setClassAlias(\Chukdo\Facades\App::class, 'App');
        Facade::setClassAlias(\Chukdo\Facades\Storage::class, 'Storage');
        Facade::setClassAlias(\Chukdo\Facades\Redis::class, 'Redis');
        Facade::setClassAlias(\Chukdo\Facades\Conf::class, 'Conf');
        Facade::setClassAlias(\Chukdo\Facades\Lang::class, 'Lang');
        Facade::setClassAlias(\Chukdo\Facades\Event::class, 'Event');
        Facade::setClassAlias(\Chukdo\Facades\Request::class, 'Request');
        Facade::setClassAlias(\Chukdo\Facades\Validator::class, 'Validator');
        Facade::setClassAlias(\Chukdo\Facades\Response::class, 'Response');
        Facade::setClassAlias(\Chukdo\Facades\View::class, 'View');
        Facade::setClassAlias(\Chukdo\Facades\Router::class, 'Router');
    }
}
