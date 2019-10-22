<?php

function dd( $data )
{
	echo '<pre>';
	print_r( $data );
	exit;
}

/** Namespaces */

use Chukdo\DB\Mongo\Server as serverMongo;
use Chukdo\DB\Elastic\Server as serverElastic;
use \Chukdo\Facades\Mongo;
use \Chukdo\Facades\Response;
use \Chukdo\Facades\View;
use \App\Providers;
use Chukdo\Json\Json;
use Chukdo\View\Functions\Basic;

$app = require __DIR__ . '/../Bootstrap/App.php';

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

$elastic   = new ServerElastic();
$dbElastic = $elastic->database();
$mongo     = new ServerMongo();
$dbMongo   = $mongo->database( 'statistiques' );
/*
$schemaAgence = $dbElastic->dropCollection( 'agence' )
				   ->createCollection( 'agence' )
				   ->schema();
$schemaAgence->set( 'agence', 'string' )
			 ->set( 'ville', 'string' )
			 ->set( 'cp', 'string' );
$schemaAgence->save();

$writeAgence = $dbElastic->collection( 'agence' )
				  ->write();
$writeAgence->set('_id', '5da9e1ebe45e3209711d6533')
			->set( 'agence', 'demo immo 33' )
			->set( 'ville', 'bordeaux' )
			->set( 'cp', '33000' )
			->insert();
$writeAgence->resetFields()
			->set('_id', '5da9e1ebe45e3209711d6536')
			->set( 'agence', 'demo immo 64' )
			->set( 'ville', 'pau' )
			->set( 'cp', '64000' )
			->insert();
$writeAgence->resetFields()
			->set('_id', '5da9e1ebe45e3209711d6534')
			->set( 'agence', 'demo immo 16' )
			->set( 'ville', 'chassors' )
			->set( 'cp', '16200' )
			->insert();
$writeAgence->resetFields()
			->set('_id', '5da9e1ece45e3209711d6535')
			->set( 'agence', 'demo immo 75' )
			->set( 'ville', 'paris' )
			->set( 'cp', '75012' )
			->insert();
/*
$schemaContrat = $db->dropCollection( 'contrat' )
					->createCollection( 'contrat' )
					->schema();
$schemaContrat->set( 'contrat', 'string' )
			  ->set( '_agence', 'string' )
			  ->set( 'completion', 'string' )
			  ->set( 'reference', 'string' );
$schemaContrat->save();

$writeContrat = $db->collection( 'contrat' )
				   ->write();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6533' )
			 ->set( 'completion', '90%' )
			 ->set( 'reference', '324324324' )
			 ->insert();
$writeContrat->set( 'contrat', 'mandat' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6533' )
			 ->set( 'completion', '40%' )
			 ->set( 'reference', '567567567657' )
			 ->insert();
$writeContrat->set( 'contrat', 'offre' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6533' )
			 ->set( 'completion', '20%' )
			 ->set( 'reference', '324324324324' )
			 ->insert();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6533' )
			 ->set( 'completion', '10%' )
			 ->set( 'reference', '111111111324' )
			 ->insert();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6534' )
			 ->set( 'completion', '10%' )
			 ->set( 'reference', '32432342324324' )
			 ->insert();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6534' )
			 ->set( 'completion', '30%' )
			 ->set( 'reference', '3ZREREZ24324324' )
			 ->insert();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ebe45e3209711d6534' )
			 ->set( 'completion', '80%' )
			 ->set( 'reference', '3243TREZER24324' )
			 ->insert();
$writeContrat->set( 'contrat', 'compromis de vente' )
			 ->set( '_agence', '5da9e1ece45e3209711d6535' )
			 ->set( 'completion', '16%' )
			 ->set( 'reference', '324324324' )
			 ->insert();
$writeContrat->set( 'contrat', 'mandat' )
			 ->set( '_agence', '5da9e1ece45e3209711d6535' )
			 ->set( 'completion', '100%' )
			 ->set( 'reference', '3243243SDFDSF24' )
			 ->insert();
*/

/*
$findContrat = $dbMongo->collection( 'contrat' )
					   ->find()
					   ->link( '_agence', [], [], 'elastic_agence', $dbElastic );

$contrats = $findContrat->all( true );
//$contrat = $contrats->get('5daae00f07612923ed6dc08a');
//$contrat->set('reference', 'num_mandat:123456')->save();
print_r( $contrats->toHtml() );
die( 'ok' );
*/

$data   = [];
$data[] = [
	'volume'  => 67,
	'edition' => 2,
];
$data[] = [
	'volume'  => 86,
	'edition' => 1,
];
$data[] = [
	'volume'  => 85,
	'edition' => 6,
];
$data[] = [
	'volume'  => 98,
	'edition' => 2,
];
$data[] = [
	'volume'  => 86,
	'edition' => 6,
];
$data[] = [
	'volume'  => 67,
	'edition' => 7,
];

$data = new Json( $data );

$collect = new \Chukdo\Json\Collect();

echo $collect->where( 'volume', '>', 68 )
			 ->orderBy( 'volume', SORT_DESC )
			 ->orderBy( 'edition', SORT_ASC )
			 ->push( $data )
			 ->values()
			 ->toHtml();

exit;

$collectionMongo = $dbMongo->collection( 'esign' );

$recordsMongo = $collectionMongo->find()
								->limit( 50 )
								->stream();

foreach ( $recordsMongo as $record ) {
	echo $record->toHtml();
}
exit;
$collectMongo = $recordsMongo->collect();

echo $recordsMongo->wildcard( '*.modeles.*._modele' )
				  ->collect()
				  ->unique()
				  ->values()
				  ->toHtml();//$collectMongo->group('date')->values()->toHtml();

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