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
use Chukdo\Helper\HttpRequest;

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

$json = new \Chukdo\Json\Json([
    [
        'prix'    => 10,
        'produit' => 'maison',
        'cp'      => 16200,
        'tva'     => 10,
        'ref'     => [
            'client' => 'orpi',
            'public' => 'ORPI',
            'prix'   => 12,
            'cp'     => 16200,
        ],
    ],
    [
        'prix'    => 10,
        'produit' => 'maison',
        'cp'      => 16200,
        'tva'     => 7,
        'ref'     => [
            'client' => 'orpi',
            'public' => 'ORPI',
            'prix'   => 100,
            'cp'     => 16200,
        ],
    ],
    [
        'prix'    => 100,
        'produit' => 'avion',
        'cp'      => 33000,
        'tva'     => 10,
        'ref'     => [
            'client' => 'doc',
            'public' => 'ORPI',
            'prix'   => 13,
        ],
    ],
    [
        'prix'    => 1000,
        'produit' => 'voiture',
        'cp'      => 64000,
        'tva'     => 20,
        'ref'     => [
            'client' => 'laforet',
            'public' => 'AC3',
            'prix'   => 14,
        ],
    ],
    [
        'prix'    => 200,
        'produit' => 'moto',
        'cp'      => 75000,
        'tva'     => 20,
        'ref'     => [
            'client' => 'cph',
            'public' => 'AC3',
            'prix'   => 15,
        ],
    ],
    [
        'prix'    => 300,
        'produit' => 'telephone',
        'cp'      => 67000,
        'tva'     => 20,
        'ref'     => [
            'client' => 'guy hoquet',
            'public' => 'IMMO-FACILE',
            'prix'   => 16,
        ],
    ],
]);

//dd($json->sort('ref.prix')->toHtml());

$json = new \Chukdo\Json\Json([
    'produit1' => [
        [
            'prix'    => 340,
            'product' => 'tel',
        ],
        [
            'prix'    => 199,
            'product' => 'tablette',
        ],
        [
            'prix'    => 500,
            'product' => 'ordinateur',
        ],
    ],
    'produit2' => [
        [
            'prix'    => 400,
            'product' => 'console',
        ],
        [
            'prix'    => 50,
            'product' => 'hub',
        ],
        [
            'prix'    => 2000,
            'product' => 'mac',
        ],
    ],
]);

$json->get('produit2')
    ->collect()
    ->with(['prix'])
    ->sort('prix', 'asc');
$json->get('produit1')
    ->collect()
    ->sort('prix', 'asc');

/**dd(
    $json->toHtml());*/

// addToSet > tva, ref.prix tcc closure
//dd(db::collection('students', 'test')->info());
/**dd($json->collect()
 * ->where('ref.public', '=', 'ORPI')
 * ->without('prix', 'ref.client')
 * ->addToSet([
 * 'tva',
 * 'ref.prix',
 * ], 'prix.ttc', function( $p )
 * {
 * return ( 1 + ( $p[ 'tva' ] / 100 ) ) * $p[ 'ref.prix' ];
 * })
 * ->without('tva', 'ref.prix')
 * ->filterKey('ref.public', function( $r )
 * {
 * return strtolower($r);
 * })
 * ->group('cp')
 * //->match('cp', '=', 'ref.cp')
 * ->values()
 * ->toHtml());*/


$agence= new \App\Model\Agence(db::collection('agence', 'test'));
$agence->init();

//dd(Conf::offsetGet('db.mongo.dsn'));
//dd(Db::collection('contrat'));

use Chukdo\Db\Mongo\Aggregate\Expr;

$json = new \Chukdo\Json\Json([
    'gender'  => 'toto',
    'year'    => '2019',
    'major'   => 'English',
    'gpa'     => '234',
    'address' => [
        'city'   => 'bordeaux',
        'street' => 'ici',
    ],
    //'titi' => ['a', 'b', 'c']
]);
//echo '<pre>';

//dd(db::collection('students', 'test')->schema()->get());

$student = db::collection('students', 'test')
    ->write()
    ->setAll($json)
    ->insert();

//print_r($student);

$up = db::collection('students', 'test')
    ->write()
    ->set('address.city', 2019)
    ->set('address.info', 'nouvelle info')
    ->pull('address.cp', '<=', 13)
    ->where('year', '=', 2019)
    ->update();
//dd($up);
//exit;
$write = db::collection('test', 'test')
    ->write();

$write->session()
    ->startTransaction([]);
$write->setAll([
    'cust_id'  => 'domingo',
    'ord_date' => new DateTime(),
    'status'   => 'A',
    'amount'   => 400,
    'a'        => [
        'b' => [
            'toto' => 'titi',
            'test' => new DateTime(),
        ],
    ],
])
    ->insert();

$write2 = db::collection('test', 'test')
    ->write();
$write2->setSession($write->session());
$write2->set('amount', 600)
    ->set('a', [
        'b' => [
            'toto2' => 'titi2',
            'date'  => new DateTime(),
        ],
    ])
    ->where('cust_id', '=', 'domingo')
    ->update();


$aggregate = Db::collection('test', 'test')
    ->aggregate()
    ->setSession($write->session())
    ->where('status', '=', 'A')
    ->pipe()
    ->group('cust_id')
    ->calculate('dates', Expr::push('ord_date'))
    ->calculate('total', Expr::sum('amount'))
    ->pipe()
    ->sort('total', 'desc');
$write2->session()
    ->commitTransaction();
/**dd($aggregate->all()
    ->toHtml());*/

$m = new \Chukdo\Db\Mongo\Aggregate\Expression('multiply', [
    'price',
    'quantity',
]);
$s = new \Chukdo\Db\Mongo\Aggregate\Expression('sum', $m);

//dd($s->projection());

$contrat = Db::collection('contrat');

dd($contrat->find()
    ->without('_id')
    ->with('_agence', '_modele', 'history.id', 'history._version')
    ->limit(4)
    ->where('version', '=', '2')
    ->where('state', '=', '1')
    ->where('history', 'size', 4)
    ->where('history._version', '=', '5a3c37db3fcd9e16e21fe0b5')
    ->all()->toHtml());
// join
// group or aggregate
//


//$contrat->write()->insert();
//$contrat->write()->set()->set()->where()->updateOne();

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
    ->set('tel', '+33626148368');

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
