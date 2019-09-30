<?php

function dd( $data )
{
	echo '<pre>';
	print_r( $data );
	exit;
}

/** Namespaces */

use Chukdo\DB\Elastic\Server;
use \Chukdo\Facades\Mongo;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\View;
use \App\Providers;
use Chukdo\Json\Json;
use Chukdo\View\Functions\Basic;

$app = require_once __DIR__ . '/../Bootstrap/App.php';

$app->channel( \Chukdo\Helper\HttpRequest::subDomain() );

$app->conf()
	->loadDefault( __DIR__ . '/../Conf/', $app->env(), $app->channel() );

$app->lang()
	->loadDir( __DIR__ . '/../Lang/' . \Chukdo\Helper\HttpRequest::tld() );

/** Service APP register */
$app->registerServices( [
	Providers\AppServiceProvider::class,
	Providers\ServiceLocatorServiceProvider::class,
	Providers\LoggerHandlerServiceProvider::class,
	Providers\ExceptionLoggerServiceProvider::class,
	Providers\ValidatorServiceProvider::class,
	Providers\MongoServiceProvider::class,
] );

$elastic = new Server();
$db      = $elastic->database();
/**$schema  = $db->dropCollection( 'test' )
 * ->createCollection( 'test' )
 * ->schema();
 * $schema->set( 'agence', 'text' )
 * ->set( 'ville', 'keyword' )
 * ->set( 'cp', 'keyword' )
 * ->set( 'rcp', 'integer' )
 * ->set('meta', [
 * 'siren' => 'keyword',
 * 'cartepro' => 'keyword',
 * 'gestion' => [
 * 'agence' => 'keyword',
 * 'adresse' => 'keyword',
 * 'cp' => 'keyword',
 * 'ville' => 'keyword'
 * ]
 * ]);
 * //dd($schema->toArray());
 *
 * $schema->save();
 * $write = $db->collection( 'test' )
 * ->write();
 * $write->setAll( [
 * 'agence' => 'editions modelo',
 * 'ville'  => 'bordeaux',
 * 'cp'     => '33000',
 * 'rcp'    => 1000,
 * 'meta'    => [
 * 'cartepro' => '0123456789',
 * 'siren' => '1234-12345-1234554321',
 * 'gestion' => [
 * 'cp' => '33300'
 * ]
 * ]
 * ] )
 * ->insert();
 *
 * $write->setAll( [
 * 'agence' => 'immo64',
 * 'ville'  => 'pau',
 * 'cp'     => '64000',
 * 'rcp'    => 10000,
 * 'meta'    => [
 * 'cartepro' => '0126523789',
 * 'siren' => '1234-12345-1234343554321',
 * 'gestion' => [
 * 'cp' => '64100'
 * ]
 * ]
 * ] )
 * ->insert();
 *
 * $write->setAll( [
 * 'agence' => 'toulouse la belle ville que voila que je veux pas y vivre',
 * 'ville'  => 'toulouse',
 * 'cp'     => '31000',
 * 'rcp'    => 100000,
 * 'meta'    => [
 * 'cartepro' => '012354749',
 * 'siren' => '1234-12345-12345542345321',
 * 'gestion' => [
 * 'cp' => '31200'
 * ]
 * ]
 * ] )
 * ->insert();*/

$find = $db->collection( 'test' )
		   ->find();

dd( $find->distinct( 'cp' ) );

die( 'ok' );
exit;
$contrat = Mongo::collection( 'contrat' );

$app->dd( $contrat->find()
				  ->without( '_id' )
				  ->with( '_agence', '_modele', 'reference', 'history.id', 'history._version' )
				  ->link( '_agence', [
					  'agence',
					  'cp',
					  'ville',
					  'date_created',
					  'date_modified',
				  ] )
				  ->limit( 4 )
				  ->where( 'version', '=', '2' )
				  ->where( 'state', '=', '1' )
	//->where('history', 'size', 4)
				  ->where( 'history._version', '=', '5a3c37db3fcd9e16e21fe0b5' )
				  ->all()
				  ->toHtml() );

Response::header( 'X-test', 'test header' );
View::setDefaultFolder( __DIR__ . '/../Views/' )
	->loadFunction( new Basic() )
	->render( 'test', [
		'title' => 'chukdo test',
		'list'  => [
			'c',
			'h',
			'u',
			'k',
			'd',
			'o',
		],
	] );