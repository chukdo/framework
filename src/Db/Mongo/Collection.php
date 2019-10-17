<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Db\Record\Record;
use Chukdo\Db\Mongo\Aggregate\Aggregate;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Helper\Str;
use Chukdo\Helper\Is;
use MongoDB\Collection as MongoDbCollection;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Timestamp;
use ReflectionClass;
use ReflectionException;
use Exception;

/**
 * Server Server Collect.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Collection implements CollectionInterface
{
	/**
	 * @var Database
	 */
	protected $database;

	/**
	 * @var MongoDbCollection
	 */
	protected $client;

	/**
	 * Collection constructor.
	 *
	 * @param Database $database
	 * @param string   $collection
	 */
	public function __construct( Database $database, string $collection )
	{
		$this->database = $database;
		$client         = $database->server()
								   ->client();
		$this->client   = new MongoDbCollection( $client, $database->name(), $collection );
	}

	/**
	 * @param string|null $field
	 * @param             $value
	 *
	 * @return mixed
	 * @throws Exception
	 */
	public static function filterOut( ?string $field, $value )
	{
		if ( $value instanceof ObjectId ) {
			return (string) $value;
		}

		if ( $value instanceof Timestamp ) {
			return ( new DateTime() )->setTimestamp( (int) (string) $value );
		}

		if ( $value instanceof UTCDateTime ) {
			return $value->toDateTime();
		}

		return $value;
	}

	/**
	 * @param string|null $field
	 * @param             $value
	 *
	 * @return mixed
	 */
	public static function filterIn( ?string $field, $value )
	{
		if ( $field === '_id' && Is::string( $value ) ) {
			$value = new ObjectId( $value );
		} else if ( $value instanceof DateTime ) {
			$value = new UTCDateTime( $value->getTimestamp() * 1000 );
		} else if ( Str::contain( $field, 'date' ) && Is::scalar( $value ) ) {
			$value = new UTCDateTime( 1000 * (int) $value );
		}

		return $value;
	}

	/**
	 * @return ObjectId
	 */
	public function id(): ObjectId
	{
		return new ObjectId();
	}

	/**
	 * @param $data
	 *
	 * @return Record|object
	 */
	public function record( $data ): Record
	{
		try {
			$reflector = new ReflectionClass( '\App\Model\Mongo\Record\\' . $this->name() );

			if ( $reflector->implementsInterface( Record::class ) ) {
				return $reflector->newInstanceArgs( [
					$this,
					$data,
				] );
			}
		} catch ( ReflectionException $e ) {
		}

		return new Record( $this, $data );
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->client()
					->getCollectionName();
	}

	/**
	 * @return MongoDbCollection
	 */
	public function client(): MongoDbCollection
	{
		return $this->client;
	}

	/**
	 * @param string      $collection
	 * @param string|null $database
	 *
	 * @return CollectionInterface
	 */
	public function rename( string $collection, string $database = null ): CollectionInterface
	{
		$oldDatabase   = $this->database()
							  ->name();
		$oldCollection = $this->name();
		$old           = $oldDatabase . '.' . $oldCollection;
		$newDatabase   = $database
			?? $oldDatabase;
		$newCollection = $collection;
		$new           = $newDatabase . '.' . $newCollection;
		$command       = $this->database()
							  ->server()
							  ->command( [
								  'renameCollection' => $old,
								  'to'               => $new,
							  ] );

		if ( $command->offsetGet( 'ok' ) === 1 ) {
			return $this->database()
						->server()
						->database( $newDatabase )
						->collection( $newCollection );
		}

		throw new MongoException( sprintf( 'Impossible de renommer la collection [%s] vers [%s]', $old, $new ) );
	}

	/**
	 * @return Database
	 */
	public function database(): DatabaseInterface
	{
		return $this->database;
	}

	/**
	 * @return bool
	 */
	public function drop(): bool
	{
		$drop = $this->client()
					 ->drop();

		return $drop[ 'ok' ] === 1;
	}

	/**
	 * @return Find
	 */
	public function find(): FindInterface
	{
		return new Find( $this );
	}

	/**
	 * @return JsonInterface
	 */
	public function info(): JsonInterface
	{
		$name   = $this->name();
		$dbName = $this->database()
					   ->name();
		$stats  = $this->database()
					   ->server()
					   ->command( [ 'collStats' => $name ], $dbName )
					   ->getIndexJson( 0 )
					   ->filter( static function( $k, $v ) {
						   if ( is_scalar( $v ) ) {
							   return $v;
						   }

						   return false;
					   } )
					   ->clean();

		return $stats;
	}

	/**
	 * @return Schema
	 */
	public function schema(): SchemaInterface
	{
		return new Schema( $this );
	}

	/**
	 * @return Write
	 */
	public function write(): WriteInterface
	{
		return new Write( $this );
	}

	/**
	 * @return Index
	 */
	public function index(): Index
	{
		return new Index( $this );
	}

	/**
	 * @return Aggregate
	 */
	public function aggregate(): Aggregate
	{
		return new Aggregate( $this );
	}
}