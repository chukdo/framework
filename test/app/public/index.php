<?php

/** Path */
define("APP_PATH", dirname(__DIR__) . '/');
define("NAME_PATH", dirname(APP_PATH) . '/');
define("BASE_PATH", dirname(NAME_PATH) . '/');
define('CONF_PATH', APP_PATH . 'Conf/');
define('LANG_PATH', APP_PATH . 'Lang/fr/');
define('VIEW_PATH', APP_PATH . 'Views/');
define('CHUKDO_PATH', BASE_PATH . 'src/');
define('VENDOR_PATH', BASE_PATH . 'vendor/');

/** Includes */
require_once CHUKDO_PATH . 'Bootstrap/Loader.php';
require_once VENDOR_PATH . 'autoload.php';

/** Namespaces */
use \Chukdo\Bootstrap;
use \Chukdo\Facades;
use \App\Providers;
use \Chukdo\Facades\App;
use \Chukdo\Facades\Conf;
use \Chukdo\Facades\Lang;
use \Chukdo\Facades\Storage;
use \Chukdo\Facades\Event;
use \Chukdo\Facades\Request;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\Router;
use \Chukdo\Facades\View;
use \Chukdo\Facades\Mongo;

/** Loader */
$loader = new Bootstrap\Loader();
$loader->registerNameSpace('\Chukdo', CHUKDO_PATH)
    ->registerNameSpace('\App', APP_PATH)
    ->register();

/** App */
$app = new Bootstrap\App();

Facades\Facade::setFacadeApplication($app,
    [
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
    ]);

/** Configuration */
Lang::loadDir(LANG_PATH);
Conf::loadFile(CONF_PATH . 'Conf.json');
App::env(App::conf('env'));
App::channel('orpi');

/** Service APP register */
$app->registerHandleExceptions()
    ->registerServices([
        Providers\AppServiceProvider::class,
        Providers\ServiceLocatorServiceProvider::class,
        Providers\LoggerHandlerServiceProvider::class,
        Providers\ExceptionLoggerServiceProvider::class,
        Providers\ValidatorServiceProvider::class,
        Providers\MongoServiceProvider::class,
    ]);

Response::header('X-test', 'test header');
View::setDefaultFolder(VIEW_PATH)
    ->loadFunction(new \Chukdo\View\Functions\Basic())
    ->render('info', []);
