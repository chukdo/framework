<?php

Namespace Chukdo\DB\Elastic;

use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Elasticsearch\Client;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Throwable;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * Server Server Database.
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
    protected $server;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $database = null;

    /**
     * Database constructor.
     *
     * @param Server      $server
     * @param string|null $database
     */
    public function __construct( Server $server, string $database = null )
    {
        $this->database = $database;
        $this->server   = $server;
        $this->client   = $server->client();
    }

    /**
     * @return string|null
     */
    public function prefixName(): ?string
    {
        return $this->name() !== null
            ? $this->name() . '_'
            : null;
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->database;
    }

    /**
     * @return Server
     */
    public function server(): Server
    {
        return $this->server;
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = true;

        foreach ( $this->collections() as $collection ) {
            $drop .= $this->collection( $collection )
                ->drop();
        }

        return $drop;
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->client;
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
     * @param string $collection
     *
     * @return Collection
     */
    public function createCollection( string $collection ): Collection
    {
        if ( !$this->collectionExist( $collection ) ) {
            $this->client()
                ->indices()
                ->create( [ 'index' => $collection ] );
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
        foreach ( $this->collections() as $coll ) {
            if ( $coll == $collection ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return JsonInterface
     */
    public function collections(): JsonInterface
    {
        $list    = new Json();
        $indices = $this->client()
            ->cat()
            ->indices();

        foreach ( $indices as $indice ) {
            if ( $this->name() !== null ) {
                if ( Str::startWith( $indice[ 'index' ], $this->name() . '_' ) ) {
                    $list->append( $indice[ 'index' ] );
                }
            } else {
                $list->append( $indice[ 'index' ] );
            }
        }

        return $list;
    }

    /**
     * @param string $collection
     *
     * @return bool
     */
    public function dropCollection( string $collection ): bool
    {
        try {
            $this->client()
                ->indices()
                ->delete( [ 'index' => $this->prefixName() . $collection ] );

            return true;
        } catch ( Missing404Exception $e ) {
            return true;
        } catch ( Throwable $e ) {
            return false;
        }
    }

    /**
     * @return JsonInterface
     */
    public function info(): JsonInterface
    {
        $stats = new Json( $this->client()
            ->indices()
            ->stats( [ 'index' => '*' ] ) );

        $info = new Json();

        foreach ( $stats->offsetGet( 'indices' ) as $key => $indice ) {
            if ( Str::startWith( $key, $this->name() ) ) {
                $info->offsetSet( $key, $indice->offsetGet( 'total' ) );
            }
        }

        return $info;
    }


}