<?php

function dd($data) {echo("Dump and Die\n" . (php_sapi_name() == 'cli' ? '' : '<pre>'));var_dump($data);exit;}

/** Definition des chemins */
DEFINE('CONF_PATH', '/storage/www/chukdo/test/app/Conf/');
DEFINE('APP_PATH', '/storage/www/chukdo/test/app/');
DEFINE('TPL_PATH', APP_PATH . 'Views/');
DEFINE('CHUKDO_PATH', '/storage/www/chukdo/src/');
DEFINE('VENDOR_PATH', '/storage/www/chukdo/vendor/');

/** Inclusion des loaders */
require_once(CHUKDO_PATH.'Bootstrap/Loader.php');
require_once(VENDOR_PATH.'autoload.php');

/** boostrap framework */
$loader = new Chukdo\Bootstrap\loader();
$loader->registerNameSpace('\Chukdo', CHUKDO_PATH);
$loader->registerNameSpace('\App', APP_PATH);
$loader->register();

$app = new \Chukdo\Bootstrap\App();

/** Declaration des facades */
Use \Chukdo\Facades\Facade;

Facade::setFacadeApplication($app);
Facade::setClassAlias(\Chukdo\Facades\Facade::class, 'Facade');
Facade::setClassAlias(\Chukdo\Facades\App::class, 'App');
Facade::setClassAlias(\Chukdo\Facades\Conf::class, 'Conf');
Facade::setClassAlias(\Chukdo\Facades\Event::class, 'Event');
Facade::setClassAlias(\Chukdo\Facades\Request::class, 'Request');
Facade::setClassAlias(\Chukdo\Facades\Response::class, 'Response');
Facade::setClassAlias(\Chukdo\Facades\View::class, 'View');
Facade::setClassAlias(\Chukdo\Facades\Router::class, 'Router');

/** Configuration */
Conf::loadConf(CONF_PATH.'Conf.json');
//Conf::loadConf(CONF_PATH.'conf_prod.json');

/** App */
App::env(App::getConf('env'));
App::channel('orpi');
App::register(\App\Providers\ServiceLocatorServiceProvider::class);
App::register(\App\Providers\LoggerHandlerServiceProvider::class);
App::register(\App\Providers\ExceptionLoggerServiceProvider::class);
App::registerHandleExceptions();

$json = new \Chukdo\Json\Json([
    'title' => 'Liste des voitures',
    'articles' => [
        'auto' => [
            'bmw',
            'audi',
            'mercédes',
            'peugeot'
        ]
    ]
]);

Response::header('X-jpd', 'de la balle');
View::setDefaultFolder(TPL_PATH);
View::loadFunction(new \Chukdo\View\Functions\Basic());
View::render('info', $json);

//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();
//Response::json($json)->send()->end();
