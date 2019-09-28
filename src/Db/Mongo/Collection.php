<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Record as RecordInterface;
use Chukdo\Db\Mongo\Record\Record;
use Chukdo\Db\Mongo\Aggregate\Aggregate;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
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
		$this->client   = new MongoDbCollection( $database->server()
														  ->client(), $database->name(), $collection );
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
			return $value->__toString();
		} else if ( $value instanceof Timestamp ) {
			return ( new DateTime() )->setTimestamp( (int) (string) $value );
		} else if ( $value instanceof UTCDateTime ) {
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
	 * @return RecordInterface
	 */
	public function record( $data ): RecordInterface
	{
		try {
			$reflector = new ReflectionClass( '\App\Model\\' . $this->name() );

			return $reflector->newInstanceArgs( [
				$this,
				$data,
			] );

		} catch ( ReflectionException $e ) {
			return new Record( $this, $data );
		}
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
	 * @return $this
	 */
	public function rename( string $collection, string $database = null ): self
	{
		$oldDatabase   = $this->database()
							  ->name();
		$oldCollection = $this->name();
		$old           = $oldDatabase . '.' . $oldCollection;
		$newDatabase   = $database
			?: $oldDatabase;
		$newCollection = $collection;
		$new           = $newDatabase . '.' . $newCollection;
		$command       = $this->database()
							  ->server()
							  ->command( [
								  'renameCollection' => $old,
								  'to'               => $new,
							  ] );

		if ( $command->offsetGet( 'ok' ) == 1 ) {
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
	public function database(): Database
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

		return $drop[ 'ok' ] == 1;
	}

	/**
	 * @return Find
	 */
	public function find(): Find
	{
		return new Find( $this );
	}

	/**
	 * @return JsonInterface
	 */
	public function info(): JsonInterface
	{
		$stats = $this->database()
					  ->server()
					  ->command( [ 'collStats' => $this->name() ], $this->database()
																		->name() )
					  ->getIndexJson( 0 )
					  ->filter( function( $k, $v ) {
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
	public function schema(): Schema
	{
		return new Schema( $this );
	}

	/**
	 * @return Write
	 */
	public function write(): Write
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