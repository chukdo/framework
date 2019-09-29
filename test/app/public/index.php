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
//$db->collection('test')->schema()->toArray();

/**
 * $db->dropCollection( 'test' );
 * $schema = $db->createCollection( 'test' )
 * ->schema();
 * $schema->set( 'first_name', 'keyword' )
 * ->set( 'last_name', 'keyword' )
 * ->set( 'age', 'integer' )
 * ->set( 'adresse', 'text', [
 * 'fields' => [
 * 'rue'   => [
 * 'type' => 'text',
 * ],
 * 'cp'    => [
 * 'type' => 'text',
 * ],
 * 'ville' => [
 * 'type' => 'text',
 * ],
 * ],
 * ] );
 * $schema->save();
 */
/**foreach ( $db->collections() as $c ) {
 * echo $c;
 * echo ( new Json( $db->collection( $c )
 * ->schema()
 * ->toArray() ) )->toHtml();
 * }
 * exit;
 * */
/**
 * $c = $db->collection( 'test2' );
 * $s = $c->schema();
 * $s->get('info')->set('a', 'text');
 *
 * $c->rename('test',null, $s);
 */

$app->dd( $db->collection( 'test' )
			 ->write()
	/**->where( '_id', 'in', [
	 * 'bbe1d1c2da8480e72758e72467d994af357250b1',
	 * 'f9a8645e5931690d1eb38562ae3cfdc8e368fd09',
	 * ] )*/
	//->where('age', '=', 37)
			 ->where( 'zz', 'exists' )
			 ->set( 'age', 3778 )
			 ->set( 'info.b.h', [
				 'a',
				 'b',
				 'c',
				 'd',
				 'e',
			 ] )
	//->addToSet( 'info.b.g', 1234569 )
	//->push( 'info.b.g', 1234567899 )
	//->unset('z')
			 ->set( 'zz', 'il est la' )
			 ->set( 'uf', [
				 'tutu' => 'vanessa et jp 2019 - 09 14 11h15',
				 'bubu' => 'bibi22',
			 ] )
			 ->updateOrInsert() );

exit;
$app->dd( $db->collection( 'test' )
			 ->write()
			 ->set( 'first_name', 'jp' )
			 ->set( 'last_name', 'domingo' )
			 ->set( 'age', 44 )
			 ->set( 'adresse', '124 rue camille godard' )
			 ->set( 'info', [
				 'a' => 'toto',
				 'b' => [ 'c1' => 'd1' ],
			 ] )
			 ->insert() );

exit;

/**$app->dd($db->collection('test')->rename('newtest'));*/

/**$elastic->collection('test')
 * ->indices()
 * ->putMapping([
 * 'index' => 'test',
 * 'body'  => [
 * 'properties' => [
 * 'first_name' => [
 * 'type'    => 'keyword',
 * 'copy_to' => 'full_name',
 * ],
 * 'last_name'  => [
 * 'type'    => 'keyword',
 * 'copy_to' => [
 * 'full_name',
 * 'testing',
 * ],
 * ],
 * 'age'        => [
 * 'type' => 'integer',
 * ],
 * 'testing'    => [
 * 'type' => 'text',
 * ],
 * 'full_name'  => [
 * 'type' => 'text',
 * ],
 * 'text'       => [
 * 'type'   => 'text',
 * 'fields' => [
 * 'english' => [
 * 'type' => 'text',
 * ],
 * 'french'  => [
 * 'type' => 'text',
 * ],
 * ],
 * ],
 * ],
 * ],
 * ]);*/

/**
 * $app->dd($elastic->collection('test')
 * ->schema()
 * ->get());
 * $app->dd($elastic->collection('test')
 * ->properties()
 * ->toArray());
 */

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