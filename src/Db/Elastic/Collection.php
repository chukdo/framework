<?php

Namespace Chukdo\Db\Elastic;

use Chukdo\Bootstrap\Loader;
use Chukdo\Conf\Conf;
use Chukdo\Facades\App;
use MongoDB\BSON\ObjectId;
use Chukdo\Db\Elastic\Schema\Schema;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Db\Record\Record;
use DateTime;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use Throwable;

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
            return ( new DateTime() )->setTimestamp( 1000 * (int) (string) $value );
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
        }
        else {
            if ( Str::contain( $field, 'date' ) && Is::scalar( $value ) ) {
                $value = 1000 * (int) $value;
            }
        }

        return $value;
    }

    /**
     * @return string
     */
    public function id(): string
    {
        return (string) new ObjectId();
    }

    /**
     * @param $data
     *
     * @return Record|object
     */
    public function record( $data ): Record
    {
        return Loader::instanceClass( App::conf()
                                         ->offsetGet( 'path.model.elastic.record' ) . $this->path(), Record::class, [
                                          $this,
                                          $data,
                                      ] ) ?? new Record( $this, $data );
    }

    /**
     * @return string
     */
    public function path(): string
    {
        $databaseName   = $this->database()
                               ->name( true );
        $collectionName = $this->name( true );

        return '\\' . $databaseName . '\\' . $collectionName;
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        return $this->database;
    }

    /**
     * @param bool $ucFirst
     *
     * @return string
     */
    public function name( bool $ucFirst = false ): string
    {
        return $ucFirst
            ? ucfirst( $this->collection )
            : $this->collection;
    }

    /**
     * @param string      $collection
     * @param string|null $database
     * @param Schema|null $schema
     *
     * @return Collection
     */
    public function rename( string $collection, string $database = null, Schema $schema = null ): Collection
    {
        $database      ??= $this->database()
                                ->name();
        $newCollection = $this->database()
                              ->server()
                              ->database( $database )
                              ->dropCollection( $collection )
                              ->createCollection( $collection );
        $newCollection->client()
                      ->indices()
                      ->putMapping( [
                                        'index' => $newCollection->fullName(),
                                        'body'  => $schema
                                            ? $schema->toArray()
                                            : $this->schema()
                                                   ->toArray(),
                                    ] );
        $this->client()
             ->reindex( [
                            'body' => [
                                'source' => [ 'index' => $this->fullName() ],
                                'dest'   => [ 'index' => $newCollection->fullName() ],
                            ],
                        ] );
        $this->drop();

        return $newCollection;
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
     * @return string
     */
    public function fullName(): string
    {
        return $this->database()
                    ->prefixName() . $this->name();
    }

    /**
     * @return Schema|object
     */
    public function schema(): Schema
    {
        return Loader::instanceClass( App::conf()
                                         ->offsetGet( 'path.model.elastic.schema' ) . $this->path(), Schema::class, [
                                          $this,
                                      ] ) ?? new Schema( $this );
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
        }
        catch ( Missing404Exception $e ) {
            return true;
        }
        catch ( Throwable $e ) {
            return false;
        }
    }

    /**
     * @return Find|object
     */
    public function find(): Find
    {
        return Loader::instanceClass( App::conf()
                                         ->offsetGet( 'path.model.elastic.find' ) . $this->path(), Find::class, [
                                          $this,
                                      ] ) ?? new Find( $this );
    }

    /**
     * @return Json
     */
    public function info(): Json
    {
        $stats = new Json( $this->client()
                                ->indices()
                                ->stats( [ 'index' => $this->fullName() ] ) );

        return $stats->getJson( 'indices.' . $this->fullName() );
    }

    /**
     * @return Write|object
     */
    public function write(): Write
    {
        return Loader::instanceClass( App::conf()
                                         ->offsetGet( 'path.model.elastic.write' ) . $this->path(), Write::class, [
                                          $this,
                                      ] ) ?? new Write( $this );
    }
}