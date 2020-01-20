<?php

/** Namespaces */

use \App\Providers;
use \Chukdo\Facades\View;
use \Chukdo\Facades\Router;
use Chukdo\Http\Input;
use Chukdo\Http\Response;
use Chukdo\View\Functions\Basic;

$app = require __DIR__ . '/../Bootstrap/App.php';

$app->channel( \Chukdo\Helper\HttpRequest::subDomain() );

$app->conf()
    ->loadDefault( __DIR__ . '/../Conf/', $app->env(), $app->channel() );

$app->lang()
    ->loadDir( __DIR__ . '/../Lang/' . \Chukdo\Helper\HttpRequest::tld( 'fr' ) );

/** Service APP register */
$app->registerServices( [
                            Providers\AppServiceProvider::class,
                            Providers\ServiceLocatorServiceProvider::class,
                            Providers\LoggerHandlerServiceProvider::class,
                            Providers\ExceptionLoggerServiceProvider::class,
                            Providers\ValidatorServiceProvider::class,
                            Providers\MongoServiceProvider::class,
                        ] );

Router::any( '/', function( Input $inputs, Response $response )
{
    $response->header( 'X-test', 'test header' );

    return View::setDefaultFolder( __DIR__ . '/../Views/' )
               ->setResponseHandler( $response )
               ->loadFunction( new Basic() )
               ->render( 'test', [
                   'title' => 'Test',
                   'list'  => [
                       '<a href="/oauth2/authorize">Check dropbox access</a>',
                       'h',
                       'u',
                       'k',
                       'd',
                       'o',
                   ],
               ] );
} );

Router::any( '/oauth2/authorize', '\App\Controlers\Oauth2@authorize' );
Router::any( '/oauth2/callback', '\App\Controlers\Oauth2@callback' );

$r = Router::route();