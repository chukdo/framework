<?php

Namespace Chukdo\Db\Mongo;

use Chukdo\Bootstrap\Loader;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Db\Record\Record;
use Chukdo\Db\Mongo\Aggregate\Aggregate;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Json\Json;
use Chukdo\Helper\Str;
use Chukdo\Helper\Is;
use MongoDB\Collection as MongoDbCollection;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Timestamp;
use Exception;

/**
 * Server Server Collect.
 *
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
    protected Database $database;

    /**
     * @var MongoDbCollection
     */
    protected MongoDbCollection $client;

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
        }
        else {
            if ( $value instanceof DateTime ) {
                $value = new UTCDateTime( $value->getTimestamp() * 1000 );
            }
            else {
                if ( Str::contain( $field, 'date' ) && Is::scalar( $value ) ) {
                    $value = new UTCDateTime( 1000 * (int) $value );
                }
            }
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
        return Loader::instanceClass( '\App\Model\Mongo\Record\\' . $this->name(), Record::class, [
                $this,
                $data,
            ] ) ?? new Record( $this, $data );
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
     * @return Collection
     */
    public function rename( string $collection, string $database = null ): Collection
    {
        $oldDatabase   = $this->database()
            ->name();
        $oldCollection = $this->name();
        $old           = $oldDatabase . '.' . $oldCollection;
        $newDatabase   = $database ?? $oldDatabase;
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
        throw new MongoException( sprintf( 'Can\'t rename collection [%s] to [%s]', $old, $new ) );
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

        return $drop[ 'ok' ] === 1;
    }

    /**
     * @return Find
     */
    public function find(): Find
    {
        return new Find( $this );
    }

    /**
     * @return Json
     */
    public function info(): Json
    {
        $name   = $this->name();
        $dbName = $this->database()
            ->name();

        return $this->database()
            ->server()
            ->command( [ 'collStats' => $name ], $dbName )
            ->getIndexJson( 0 )
            ->filter( fn( $k, $v ) => is_scalar( $v )
                ? $v
                : false )
            ->clean();
    }

    /**
     * @return Schema|object
     */
    public function schema(): Schema
    {
        return Loader::instanceClass( '\App\Model\Mongo\Schema\\' . $this->name(), Schema::class, [
                $this,
            ] ) ?? new Schema( $this );
    }

    /**
     * @return Write|object
     */
    public function write(): Write
    {
        return Loader::instanceClass( '\App\Model\Mongo\Write\\' . $this->name(), Write::class, [
                $this,
            ] ) ?? new Write( $this );
    }

    /**
     * @return Index|object
     */
    public function index(): Index
    {
        return Loader::instanceClass( '\App\Model\Mongo\Index\\' . $this->name(), Index::class, [
                $this,
            ] ) ?? new Index( $this );
    }

    /**
     * @return Aggregate|object
     */
    public function aggregate(): Aggregate
    {
        return Loader::instanceClass( '\App\Model\Mongo\Aggregate\\' . $this->name(), Aggregate::class, [
                $this,
            ] ) ?? new Aggregate( $this );
    }
}