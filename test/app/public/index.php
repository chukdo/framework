<?php

function dd( $data )
{
    echo "Dump and Die\n" . (php_sapi_name() == 'cli'
            ? ''
            : '<pre>');
    var_dump($data);
    exit;
}

/* Definition des chemins */
define('LANG_PATH', '/storage/www/chukdo/test/app/Lang/fr/');
define('CONF_PATH', '/storage/www/chukdo/test/app/Conf/');
define('APP_PATH', '/storage/www/chukdo/test/app/');
define('TPL_PATH', APP_PATH . 'Views/');
define('CHUKDO_PATH', '/storage/www/chukdo/src/');
define('VENDOR_PATH', '/storage/www/chukdo/vendor/');

/** Inclusion des loaders */
require_once CHUKDO_PATH . 'Bootstrap/Loader.php';
require_once VENDOR_PATH . 'autoload.php';

/** boostrap framework */
$loader = new \Chukdo\Bootstrap\Loader();
$loader->registerNameSpace('\Chukdo', CHUKDO_PATH);
$loader->registerNameSpace('\App', APP_PATH);
$loader->register();

/* App */
$app = new \Chukdo\Bootstrap\App();
$app->registerHandleExceptions();
$app->register(\App\Providers\FacadeServiceProvider::class);
$app->register(\App\Providers\ServiceLocatorServiceProvider::class);
$app->register(\App\Providers\LoggerHandlerServiceProvider::class);
$app->register(\App\Providers\ExceptionLoggerServiceProvider::class);
$app->register(\App\Providers\ValidatorServiceProvider::class);

/* Configuration */
Lang::loadDir(LANG_PATH);
//dd(Lang::all());
Conf::loadFile(CONF_PATH . 'Conf.json');
//Conf::load(CONF_PATH.'conf_prod.json');

App::env(App::getConf('env'));
App::channel('orpi');

$json = new \Chukdo\Json\Json([
        'title'    => 'Liste des voitures',
        'articles' => [
            'auto' => [
                'bmw',
                'audi',
                'mercédes',
                'peugeot',
            ],
        ],
    ]);

Request::Inputs()
    ->set('csrf', \Chukdo\Helper\Crypto::encodeCsrf(60, Conf::get('salt')));
Request::Inputs()
    ->set('tel', '+33626148328');

$validator = Request::validate([
    'tel'  => 'required|phone',
    'csrf' => 'required|csrf:@salt',
]);

//echo '<html><body><form method="POST" action="/index.php" enctype="multipart/form-data"><input type="file" name="dossier"><input type="submit"></form></body>';

if( $validator->fails() ) {
    dd($validator->errors());
}
else {
    dd($validator->validated());
}

Response::header('X-jpd',
    'de la balle');
View::setDefaultFolder(TPL_PATH);
View::loadFunction(new \Chukdo\View\Functions\Basic());
View::render('info',
    $json);

//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();
//Response::json($json)->send()->end();
