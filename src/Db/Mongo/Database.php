<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Database as MongoDbDatabase;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Mongo Mongo Database.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Database
{
    /**
     * @var Mongo
     */
    protected $mongo;

    /**
     * @var MongoDbDatabase
     */
    protected $database;

    /**
     * Database constructor.
     * @param Mongo  $mongo
     * @param string $database
     */
    public function __construct( Mongo $mongo, string $database )
    {
        $this->mongo    = $mongo;
        $this->database = new MongoDbDatabase($mongo->mongoManager(), $database);
    }

    /**
     * @return bool
     */
    public function repair(): bool
    {
        return $this->mongo()
                   ->command([
                       'repairDatabase' => 1,
                   ], $this->name())
                   ->get('0.ok') == 1;
    }

    /**
     * @return Mongo
     */
    public function mongo(): Mongo
    {
        return $this->mongo;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->mongoDatabase()
            ->getDatabaseName();
    }

    /**
     * @return MongoDbDatabase
     */
    public function mongoDatabase(): MongoDbDatabase
    {
        return $this->database;
    }

    /**
     * @return JsonInterface
     */
    public function stat(): JsonInterface
    {
        $stats = $this->mongo()
            ->command([ 'dbStats' => 1 ], $this->name())
            ->getIndex(0, new Json())
            ->filter(function( $k, $v )
            {
                if ( is_scalar($v) ) {
                    return $v;
                }

                return false;
            })
            ->clean();

        return $stats;
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->mongoDatabase()
            ->drop();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @return JsonInterface
     */
    public function collections(): JsonInterface
    {
        $list = new Json();

        foreach ( $this->mongoDatabase()
            ->listCollections() as $collection ) {
            $list->append($collection->getName());
        }

        return $list;
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function createCollection( string $collection ): Collection
    {
        if ( !$this->collectionExist($collection) ) {
            $this->mongoDatabase()
                ->createCollection($collection);
        }

        return $this->collection($collection);
    }

    /**
     * @param string $collection
     * @return bool
     */
    public function collectionExist( string $collection ): bool
    {
        foreach ( $this->mongoDatabase()
            ->listCollections() as $coll ) {
            if ( $coll->getName() == $collection ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return new Collection($this->mongo(), $this->name(), $collection);
    }
}