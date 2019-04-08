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
use \Chukdo\Bootstrap;
use \Chukdo\Facades;
use \App\Providers;

/* Loader */
$loader = new Bootstrap\Loader();
$loader->registerNameSpace('\Chukdo', CHUKDO_PATH)
    ->registerNameSpace('\App', APP_PATH)
    ->register();

/* App */
$app = new Bootstrap\App();
$app->registerHandleExceptions()
    ->registerServices([
        Providers\ServiceLocatorServiceProvider::class,
        Providers\LoggerHandlerServiceProvider::class,
        Providers\ExceptionLoggerServiceProvider::class,
        Providers\ValidatorServiceProvider::class,
    ]);

/* Facades */
Facades\Facade::setFacadeApplication($app,
    [
        'Facade'    => Facades\Facade::class,
        'App'       => Facades\App::class,
        'Redis'     => Facades\Redis::class,
        'Conf'      => Facades\Conf::class,
        'Lang'      => Facades\Lang::class,
        'Event'     => Facades\Event::class,
        'Request'   => Facades\Request::class,
        'Validator' => Facades\Validator::class,
        'Response'  => Facades\Response::class,
        'View'      => Facades\View::class,
        'Router'    => Facades\Router::class,
    ]);

/* Configuration */
Lang::loadDir(LANG_PATH);
Conf::loadFile(CONF_PATH . 'Conf.json');
App::env(App::conf('env'));
App::channel('orpi');

$route = new \Chukdo\Routing\Route('GET', 'user/{id}', Request::instance(), function($request) {
    dd($request->inputs());
});
$route->match();
dd(Request::inputs());

dd(parse_url('https://{projkey}.modelo.fr/user/{id}'));
// route
// Router::get('{projkey}.modelo.fr/user/{id}', function() {});
// Router::get('user/{id}', function() {});
// get closure
// get controler@action
// route::match([] , regex
//route::redirect 302
//permanentRedirect 301
//route::view(route, file, data)
//route::get('user/{id}', function($id) {}
//route::get('user/{id?}', function($id = null) {}
//route::get()->where('a', '32', 'b' => '[a-z]+
//// route::name() = utile pour appel d'une route
/// $request->route()->named('profile')
//Route::namespace('Admin')->group(function () {
// Controllers Within The "App\Http\Controllers\Admin" Namespace
//});
/**Route::domain('{account}.myapp.com')->group(function () {
 * Route::get('user/{id}', function ($account, $id) {
 * //
 * });
 * });
 * Route::get('api/users/{user}', function (App\User $user) {
 * return $user->email;
 * });
 */
//Route::fallback
//Route::middleware('auth:api')->group(function () {
//$route = Route::current();
//$name = Route::currentRouteName();
//$action = Route::currentRouteAction();

// Route::get('api/user/{userid}')
// Route::get('api/user/{userid?}')
// Route::get('api/user/{userid}')->where('userid', //)->wheres([])

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

Response::header('X-jpd', 'de la balle');
View::setDefaultFolder(TPL_PATH);
View::loadFunction(new \Chukdo\View\Functions\Basic());
View::render('info', $json);

//ExceptionLogger::emergency('coucou les loulous');
//Response::file('azure://files-dev/566170fe8bc5d2cf3d000000/5948da9a28b8b.pdf')->send()->end();
//Response::json($json)->send()->end();
