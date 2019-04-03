<?php

function dd($data)
{
    echo "Dump and Die\n" . (php_sapi_name() == 'cli'
        ? ''
        : '<pre>');
    var_dump($data);
    exit;
}

/* Definition des chemins */
define(
    'LANG_PATH',
    '/storage/www/chukdo/test/app/Lang/fr/'
);
define(
    'CONF_PATH',
    '/storage/www/chukdo/test/app/Conf/'
);
define(
    'APP_PATH',
    '/storage/www/chukdo/test/app/'
);
define(
    'TPL_PATH',
    APP_PATH . 'Views/'
);
define(
    'CHUKDO_PATH',
    '/storage/www/chukdo/src/'
);
define(
    'VENDOR_PATH',
    '/storage/www/chukdo/vendor/'
);

/** Inclusion des loaders */
require_once CHUKDO_PATH . 'Bootstrap/Loader.php';
require_once VENDOR_PATH . 'autoload.php';

/** boostrap framework */
$loader = new \Chukdo\Bootstrap\Loader();
$loader->registerNameSpace(
    '\Chukdo',
    CHUKDO_PATH
);
$loader->registerNameSpace(
    '\App',
    APP_PATH
);
$loader->register();

$app = new \Chukdo\Bootstrap\App();

/* Declaration des facades */

use Chukdo\Facades\Facade;

Facade::setFacadeApplication($app);
Facade::setClassAlias(
    \Chukdo\Facades\Facade::class,
    'Facade'
);
Facade::setClassAlias(
    \Chukdo\Facades\App::class,
    'App'
);
Facade::setClassAlias(
    \Chukdo\Facades\Storage::class,
    'Storage'
);
Facade::setClassAlias(
    \Chukdo\Facades\Conf::class,
    'Conf'
);
Facade::setClassAlias(
    \Chukdo\Facades\Lang::class,
    'Lang'
);
Facade::setClassAlias(
    \Chukdo\Facades\Event::class,
    'Event'
);
Facade::setClassAlias(
    \Chukdo\Facades\Request::class,
    'Request'
);
Facade::setClassAlias(
    \Chukdo\Facades\Validator::class,
    'Validator'
);
Facade::setClassAlias(
    \Chukdo\Facades\Response::class,
    'Response'
);
Facade::setClassAlias(
    \Chukdo\Facades\View::class,
    'View'
);
Facade::setClassAlias(
    \Chukdo\Facades\Router::class,
    'Router'
);

/* Configuration */
Lang::loadDir(LANG_PATH);
//dd(Lang::all());
Conf::loadFile(CONF_PATH . 'Conf.json');
//Conf::load(CONF_PATH.'conf_prod.json');



/* App */
App::env(App::getConf('env'));
App::channel('orpi');
App::register(\App\Providers\ServiceLocatorServiceProvider::class);
App::register(\App\Providers\LoggerHandlerServiceProvider::class);
App::register(\App\Providers\ExceptionLoggerServiceProvider::class);
App::registerHandleExceptions();
App::register(\App\Providers\ValidatorServiceProvider::class);

$json = new \Chukdo\Json\Json(
    [
        'title'    => 'Liste des voitures',
        'articles' => [
            'auto' => [
                'bmw',
                'audi',
                'mercédes',
                'peugeot',
            ],
        ],
    ]
);

Request::Inputs()->set('csrf', \Chukdo\Helper\Crypto::encodeCsrf(60, 'azerty'));
Request::Inputs()->set('tel', '+33626148328');

$validator = Request::validate([
    'tel' => 'required|&phone|phone',
    'csrf'=> 'required|csrf:azerty'
    //'title'      => 'required|array:2,3',
    //'title.*.cp' => 'required|array:1,2|string:2|striptags|label:code postal',
    //'x' => 'required|array|&striptags|string:2',
    //'dossier' => 'required|file:sheet',
    //'prix' => 'required|&float|float'
    //'title.name' => 'required|string:3,6',
    //'title.cp'   => 'required|string:5|label:code postal',
]);

//echo '<html><body><form method="POST" action="/index.php" enctype="multipart/form-data"><input type="file" name="dossier"><input type="submit"></form></body>';

if ($validator->fails()) {
    dd($validator->errors());
} else {
    dd($validator->validated());
}

Response::header(
    'X-jpd',
    'de la balle'
);
View::setDefaultFolder(TPL_PATH);
View::loadFunction(new \Chukdo\View\Functions\Basic());
View::render(
    'info',
    $json
);

//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();
//Response::json($json)->send()->end();
