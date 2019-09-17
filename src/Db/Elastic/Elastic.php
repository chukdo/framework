<?php

Namespace Chukdo\DB\Elastic;

use Chukdo\Json\Json;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Elasticsearch\Common\Exceptions\Missing404Exception;

/**
 * Server Server.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Elastic
{
    /**
     * @var string|null
     */
    protected $dsn = null;

    /**
     * @var ClientBuilder
     */
    protected $client;

    /**
     * Elastic constructor.
     * @param string|null $dsn
     * @param bool        $synchronous
     */
    public function __construct( string $dsn = null, bool $synchronous = true )
    {
        $this->dsn    = $dsn
            ?: 'localhost:9200';
        $this->client = ClientBuilder::create()
            ->setHosts(explode(',',
                $this->dsn))
            ->setHandler($synchronous
                ? ClientBuilder::singleHandler()
                : ClientBuilder::multiHandler())
            ->build();
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return $this->client()
            ->ping();
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return (string) $this->status()
            ->get('version.number');
    }

    /**
     * @return JsonInterface
     */
    public function status(): JsonInterface
    {
        return new Json($this->client()
            ->info());
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return $this->createCollection($collection);
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function createCollection( string $collection ): Collection
    {
        if ( !$this->collectionExist($collection) ) {
            $this->client()
                ->indices()
                ->create([ 'index' => $collection ]);
        }

        return new Collection($this, $collection);
    }

    /**
     * @param string $collection
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
        $list = new Json();

        foreach ( $this->client()
            ->cat()
            ->indices([ 'index' => '*' ]) as $indice ) {
            $list->append($indice[ 'index' ]);
        }

        return $list;
    }

    /**
     * @param string $collection
     * @return bool
     */
    public function dropCollection( string $collection ): bool
    {
        try {
            $this->client()
                ->indices()
                ->delete([ 'index' => $collection ]);

            return true;
        } catch ( Missing404Exception $e ) {
            return false;
        }
    }
}