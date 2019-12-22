<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Database as MongoDbDatabase;
use Chukdo\Contracts\Db\Database as DatabaseInterface;

/**
 * Server Server Database.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Database implements DatabaseInterface
{
    /**
     * @var Server
     */
    protected Server $server;

    /**
     * @var MongoDbDatabase
     */
    protected MongoDbDatabase $client;

    /**
     * Database constructor.
     *
     * @param Server $server
     * @param string $database
     */
    public function __construct( Server $server, string $database = null )
    {
        $this->server = $server;
        $this->client = new MongoDbDatabase( $server->client(), $database ?? 'main' );
    }

    /**
     * @return bool
     */
    public function repair(): bool
    {
        return $this->server()
                    ->command( [ 'repairDatabase' => 1, ], $this->name() )
                    ->get( '0.ok' ) === 1;
    }

    /**
     * @return Server
     */
    public function server(): Server
    {
        return $this->server;
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->client()
                    ->getDatabaseName();
    }

    /**
     * @return MongoDbDatabase
     */
    public function client(): MongoDbDatabase
    {
        return $this->client;
    }

    /**
     * @param string $collection
     *
     * @return Database
     */
    public function dropCollection( string $collection ): Database
    {
        $this->client()
             ->dropCollection( $collection );

        return $this;
    }

    /**
     * @param string $collection
     *
     * @return Collection
     */
    public function createCollection( string $collection ): Collection
    {
        if ( !$this->collectionExist( $collection ) ) {
            $this->client()
                 ->createCollection( $collection );
        }

        return $this->collection( $collection );
    }

    /**
     * @param string $collection
     *
     * @return bool
     */
    public function collectionExist( string $collection ): bool
    {
        return $this->collections()
                    ->in( $collection );
    }

    /**
     * @return Json
     */
    public function collections(): Json
    {
        $list = new Json();
        foreach ( $this->client()
                       ->listCollections() as $collection ) {
            $list->append( $collection->getName() );
        }

        return $list;
    }

    /**
     * @param string $collection
     *
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return new Collection( $this, $collection );
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
     * @return Json
     */
    public function info(): Json
    {
        return $this->server()
                    ->command( [ 'dbStats' => 1 ], $this->name() )
                    ->getIndexJson( 0 )
                    ->filter( fn( $k, $v ) => is_scalar( $v )
                        ? $v
                        : false )
                    ->clean();
    }
}