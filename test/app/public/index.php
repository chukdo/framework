<?php

function dd( $data )
{
    echo "Dump and Die\n" . ( php_sapi_name() == 'cli'
            ? ''
            : '<pre>' );
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

/* Facades */
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
        'Redis'    => Facades\Redis::class,
    ]);

use \Chukdo\Facades\App;
use \Chukdo\Facades\Storage;
use \Chukdo\Facades\Conf;
use \Chukdo\Facades\Lang;
use \Chukdo\Facades\Event;
use \Chukdo\Facades\Request;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\Router;
use \Chukdo\Facades\Db;

/* Configuration */
Lang::loadDir(LANG_PATH);
Conf::loadFile(CONF_PATH . 'Conf.json');
App::env(App::conf('env'));
App::channel('orpi');

$app->registerHandleExceptions()
    ->registerServices([
        Providers\AppServiceProvider::class,
        Providers\ServiceLocatorServiceProvider::class,
        Providers\LoggerHandlerServiceProvider::class,
        Providers\ExceptionLoggerServiceProvider::class,
        Providers\ValidatorServiceProvider::class,
        Providers\DbServiceProvider::class,
    ]);

/**
 * Class QuoteMiddleWare
 */
class QuoteMiddleWare implements \Chukdo\Contracts\Middleware\Middleware
{
    /**
     * @param \Chukdo\Middleware\Dispatcher $dispatcher
     * @return \Chukdo\Http\Response
     */
    public function process( \Chukdo\Middleware\Dispatcher $dispatcher ): \Chukdo\Http\Response
    {
        $response = $dispatcher->handle();

        if ( $response->getHeaders()
                 ->getStatus() == 200 ) {
            $response->prepend('"');
            $response->append('"');
        }

        return $response;
    }
}

/**
 * Class UnderscoreMiddleWare
 */
class UnderscoreMiddleWare implements \Chukdo\Contracts\Middleware\Middleware
{
    /**
     * @param \Chukdo\Middleware\Dispatcher $dispatcher
     * @return \Chukdo\Http\Response
     */
    public function process( \Chukdo\Middleware\Dispatcher $dispatcher ): \Chukdo\Http\Response
    {
        $response = $dispatcher->handle();

        if ( $response->getHeaders()
                 ->getStatus() == 200 ) {
            $response->prepend('__');
            $response->append('__');
        }
        return $response;
    }
}

/**
 * Class TraitMiddleWare
 */
class TraitMiddleWare implements \Chukdo\Contracts\Middleware\Middleware
{
    /**
     * @param \Chukdo\Middleware\Dispatcher $dispatcher
     * @return \Chukdo\Http\Response
     */
    public function process( \Chukdo\Middleware\Dispatcher $dispatcher ): \Chukdo\Http\Response
    {
        $response = $dispatcher->handle();

        if ( $response->getHeaders()
                 ->getStatus() == 200 ) {
            $response->prepend('--');
            $response->append('--');
        }

        return $response;
    }
}

//dd(Conf::offsetGet('db.mongo.dsn'));
//dd(Db::collection('contrat'));


$contrat = Db::collection('contrat')
    ->query();
dd($contrat->limit(3)->get()->all()->toHtml());
$contrat->or('qty')
    ->exists()
    ->nin([
        1,
        5,
        9,
    ]);
$contrat->or('price')
    ->gte(20)
    ->lt(10);
$contrat->and('qty')
    ->exists()
    ->nin([
        1,
        5,
        9,
    ]);

$contrat->and('agences')
    ->match(
        $contrat->field('production')
            ->eq('xyz'),
        $contrat->field('score')
            ->gt(8)
    );

dd($contrat->get());
//$contrat->set()->push()->where()->update();

Request::Inputs()
    ->set('csrf', \Chukdo\Helper\Crypto::encodeCsrf(60, Conf::get('salt')));
Request::Inputs()
    ->set('tel', '+33626148328');

Router::middleware([
    '@middleware.quote',
    '@middleware.under',
])
    ->validator([
        'a'    => 'required|string:3',
        'tel'  => 'required|phone',
        'csrf' => 'required|csrf:@salt',
    ])
    ->prefix('test')
    ->group(function()
    {
        Router::console('/toto',
            function( $inputs, $response )
            {
                return $response->content('all_toto2');
            });
        Router::get('/user/{a}/test/{comment}', 'App\Controlers\Test@index')
            ->where('a', '[a-z0-9]+')
            ->middleware([ TraitMiddleWare::class ]);
    });

Router::middleware([
    QuoteMiddleWare::class,
    TraitMiddleWare::class,
])
    ->validator([
        'a'    => 'required|string:3',
        'tel'  => 'required|phone',
        'csrf' => 'required|csrf:@salt',
    ])
    ->group(function()
    {
        Router::get('/user/{a}/test/{comment}',
            function( $inputs, $response )
            {
                return $response->content('toto2-2');
            })
            ->where('a', '[a-z0-9]+');
    });

Router::fallback(function()
{
    dd('rien de valide');
});
Router::route();
exit;

//route::redirect 302
//permanentRedirect 301

//Route::namespace('Admin')->group(function () {
// Controllers Within The "App\HttpRequest\Controllers\Admin" Namespace
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

if ( $validator->fails() ) {
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
