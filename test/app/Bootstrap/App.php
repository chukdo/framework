<?php

/** Namespaces */

use \Chukdo\Bootstrap\Loader;
use \Chukdo\Bootstrap\App;
use \Chukdo\Facades;

/** Includes */
require_once __DIR__ . '/../../../vendor/autoload.php';

/** Loader */
$loader = new Loader();
$loader->registerNameSpace( '\Chukdo', __DIR__ . '/../../src/' )
       ->registerNameSpace( '\App', __DIR__ . '/../' )
       ->register();

/** App */
$app = new App();

/** Exception Handler */
$app->registerHandleExceptions();

/** Facades Register */
Facades\Facade::setFacadeApplication( $app, [
    'Facade'   => Facades\Facade::class,
    'App'      => Facades\App::class,
    'Storage'  => Facades\Storage::class,
    'Conf'     => Facades\Conf::class,
    'Lang'     => Facades\Lang::class,
    'Event'    => Facades\Event::class,
    'Request'  => Facades\Request::class,
    'Response' => Facades\Response::class,
    'View'     => Facades\View::class,
    'Router'   => Facades\Router::class,
] );

return $app;