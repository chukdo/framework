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

use \Chukdo\Facades\App;
use \Chukdo\Facades\Storage;
use \Chukdo\Facades\Conf;
use \Chukdo\Facades\Lang;
use \Chukdo\Facades\Event;
use Chukdo\Helper\HttpRequest;
use \Chukdo\Facades\Request;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\Router;
use \Chukdo\Facades\Mongo;

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
        Providers\MongoServiceProvider::class,
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

$json2 = new \Chukdo\Json\Json([
    [
        'tva' => 10,
        'ref' => [
            'client' => 'doc12',
            'public' => 'ORPI',
        ],
    ],
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

//dd($json->diff($json2)->toHtml());

$json2->get('produit2')
    ->collect()
    ->with([ 'prix' ])
    ->sort('prix', 'asc');
$json2->get('produit1')
    ->collect()
    ->sort('prix', 'asc');

/**
 * dd($json->collect()
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

use \App\Model\Agence;

$agence = new Agence(Mongo::collection('agence', 'test'));
$agence->init();

// agence->find()->update()
// agence->new() = new record !!! ->save() or softDelete() or delete()

//dd(Conf::offsetGet('db.mongo.dsn'));
//dd(Db::collection('contrat'));

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

$student = Mongo::collection('students', 'test')
    ->write()
    ->setAll($json)
    ->insert();

//print_r($student);

$up = Mongo::collection('students', 'test')
    ->write()
    ->set('address.city', 2019)
    ->set('address.info', 'nouvelle info')
    ->pull('address.cp', '<=', 13)
    ->where('year', '=', 2019)
    ->update();
//dd($up);
//exit;
$write = Mongo::collection('test', 'test')
    ->write()
    ->startTransaction()
    ->setAll([
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
    ]);
$write->insert();

$write2 = Mongo::collection('test', 'test')
    ->write()
    ->setSession($write->session())
    ->set('amount', 600)
    ->set('a', [
        'b' => [
            'toto2' => 'titi2',
            'date'  => new DateTime(),
        ],
    ])
    ->where('cust_id', '=', 'domingo')
    ->update();

use \Chukdo\Db\Mongo\Aggregate\Expr;
use \Chukdo\Db\Mongo\Aggregate\Expression;

$aggregate = Mongo::collection('test', 'test')
    ->aggregate()
    ->where('status', '=', 'A')
    ->pipe()
    ->group('cust_id')
    ->calculate('dates', Expr::push('ord_date'))
    ->calculate('total', Expr::sum('amount'))
    ->pipe()
    ->sort('total', 'desc');
$aAll      = $aggregate->all();

//dd($aAll->toHtml());
/**dd($aggregate->all()
 * ->toHtml());*/

$m = new Expression('multiply', [
    'price',
    'quantity',
]);
$s = new Expression('sum', $m);

//dd($s->projection());

$contrat = Mongo::collection('contrat');

$listing = $contrat->find()
    ->without('_id')
    ->with('_agence', '_modele', 'reference', 'history.id', 'history._version')
    ->link('_agence', [
        'agence',
        'cp',
        'ville',
        'date_created',
        'date_modified',
    ])
    ->limit(4)
    ->where('version', '=', '2')
    ->where('state', '=', '1')
    //->where('history', 'size', 4)
    ->where('history._version', '=', '5a3c37db3fcd9e16e21fe0b5');
$r       = $listing->one();
$r->delete($write->session());
$write->abortTransaction();

dd($contrat->find()
    ->without('_id')
    ->with('_agence', '_modele', 'reference', 'history.id', 'history._version')
    ->link('_agence', [
        'agence',
        'cp',
        'ville',
        'date_created',
        'date_modified',
    ])
    ->limit(4)
    ->where('version', '=', '2')
    ->where('state', '=', '1')
    //->where('history', 'size', 4)
    ->where('history._version', '=', '5a3c37db3fcd9e16e21fe0b5')
    ->one()
    ->toHtml());

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

Response::header('X-jpd', 'de la balle');
View::setDefaultFolder(TPL_PATH);
View::loadFunction(new \Chukdo\View\Functions\Basic());
View::render('info', $json);
