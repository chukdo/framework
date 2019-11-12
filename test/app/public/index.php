<?php

/** Namespaces */

use \Chukdo\Facades\Request;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\View;
use \Chukdo\Facades\Router;
use \App\Providers;
use Chukdo\View\Functions\Basic;

$app = require __DIR__ . '/../Bootstrap/App.php';

$app->channel( \Chukdo\Helper\HttpRequest::subDomain() );

$app->conf()
    ->loadDefault( __DIR__ . '/../Conf/', $app->env(), $app->channel() );

$app->lang()
    ->loadDir( __DIR__ . '/../Lang/' . \Chukdo\Helper\HttpRequest::tld() );

/** Service APP register */
$app->registerServices( [ Providers\AppServiceProvider::class,
                          Providers\ServiceLocatorServiceProvider::class,
                          Providers\LoggerHandlerServiceProvider::class,
                          Providers\ExceptionLoggerServiceProvider::class,
                          Providers\ValidatorServiceProvider::class,
                          Providers\MongoServiceProvider::class, ] );

Router::any( '/', function( $inputs, $response )
{
    $response->header( 'X-test', 'test header' );

    return View::setDefaultFolder( __DIR__ . '/../Views/' )
               ->setResponseHandler( $response )
               ->loadFunction( new Basic() )
               ->render( 'test', [ 'title' => 'chukdo test 2',
                                   'list'  => [ 'c',
                                                'h',
                                                'u',
                                                'k',
                                                'd',
                                                'o', ], ] );
} );

Router::any( '/info', '\App\Controlers\Info@index' );

$r = Router::route();