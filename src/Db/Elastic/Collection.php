<?php

Namespace Chukdo\DB\Elastic;

use MongoDB\BSON\ObjectId;
use Chukdo\Db\Elastic\Schema\Schema;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Db\Record\Record;
use DateTime;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use ReflectionException;
use Throwable;
use ReflectionClass;

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
     * @var string
     */
    protected string $collection;

    /**
     * Collection constructor.
     *
     * @param Database $database
     * @param string   $collection
     */
    public function __construct( Database $database, string $collection )
    {
        $this->database   = $database;
        $this->collection = $collection;
    }

    /**
     * @param string|null $field
     * @param             $value
     *
     * @return DateTime|mixed
     * @throws Exception
     */
    public static function filterOut( ?string $field, $value )
    {
        if ( Str::contain( $field, 'date' ) ) {
            return ( new DateTime() )->setTimestamp( 1000 * (int)(string)$value );
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
        if ( $value instanceof DateTime ) {
            $value = $value->getTimestamp() * 1000;
        } else {
            if ( Str::contain( $field, 'date' ) && Is::scalar( $value ) ) {
                $value = 1000 * (int)$value;
            }
        }

        return $value;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return (string)new ObjectId();
    }

    /**
     * @param $data
     *
     * @return Record|object
     */
    public function record( $data ): Record
    {
        try {
            $reflector = new ReflectionClass( '\App\Model\Elastic\Record\\' . $this->name() );
            if ( $reflector->implementsInterface( Record::class ) ) {
                return $reflector->newInstanceArgs( [ $this,
                                                      $data, ] );
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
        return $this->collection;
    }

    /**
     * @param string      $collection
     * @param string|null $database
     * @param Schema|null $schema
     *
     * @return CollectionInterface
     */
    public function rename( string $collection, string $database = null, Schema $schema = null ): CollectionInterface
    {
        $database      = $database ?? $this->database()
                                           ->name();
        $newCollection = $this->database()
                              ->server()
                              ->database( $database )
                              ->dropCollection( $collection )
                              ->createCollection( $collection );
        $newCollection->client()
                      ->indices()
                      ->putMapping( [ 'index' => $newCollection->fullName(),
                                      'body'  => $schema
                                          ? $schema->toArray()
                                          : $this->schema()
                                                 ->toArray(), ] );
        $this->client()
             ->reindex( [ 'body' => [ 'source' => [ 'index' => $this->fullName() ],
                                      'dest'   => [ 'index' => $newCollection->fullName() ], ], ] );
        $this->drop();

        return $newCollection;
    }

    /**
     * @return DatabaseInterface
     */
    public function database(): DatabaseInterface
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function fullName(): string
    {
        return $this->database()
                    ->prefixName() . $this->name();
    }

    /**
     * @return Schema
     */
    public function schema(): SchemaInterface
    {
        return new Schema( $this );
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->database()
                    ->client();
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        try {
            $this->client()
                 ->indices()
                 ->delete( [ 'index' => $this->fullName() ] );

            return true;
        } catch ( Missing404Exception $e ) {
            return true;
        } catch ( Throwable $e ) {
            return false;
        }
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
        $stats = new Json( $this->client()
                                ->indices()
                                ->stats( [ 'index' => $this->name() ] ) );

        return $stats->getJson( 'indices.' . $this->fullName() );
    }

    /**
     * @return Write
     */
    public function write(): WriteInterface
    {
        return new Write( $this );
    }
}