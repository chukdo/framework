<?php

Namespace Chukdo\Db\Elastic;

use Chukdo\Json\Json;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Chukdo\Contracts\Db\Server as ServerInterface;

/**
 * Server Server.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Server implements ServerInterface
{
    /**
     * @var string|null
     */
    protected ?string $dsn = null;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var string|null
     */
    protected ?string $database = null;

    /**
     * Server constructor.
     *
     * @param string|null $dsn
     * @param string|null $database
     * @param bool        $synchronous
     */
    public function __construct( string $dsn = null, string $database = null, bool $synchronous = true )
    {
        $this->dsn      = $dsn ?? 'localhost:9200';
        $this->database = $database;
        $this->client   = ClientBuilder::create()
                                       ->setHosts( explode( ',', $this->dsn ) )
                                       ->setHandler( $synchronous
                                                         ? ClientBuilder::singleHandler()
                                                         : ClientBuilder::multiHandler() )
                                       ->build();
    }

    /**
     * @param array       $args
     * @param string|null $db
     *
     * @return Json
     */
    public function command( array $args, string $db = null ): Json
    {
        throw new ElasticException( 'elasticsearch Command no exist' );
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'Elastic';
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
     * @return Json
     */
    public function databases(): Json
    {
        $databases = new Json();
        foreach ( $this->client()
                       ->cat()
                       ->indices( [ 'index' => '*' ] ) as $indice ) {
            $databases->append( $indice[ 'index' ] );
        }

        return $databases;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return (string) $this->status()
                             ->get( 'version.number' );
    }

    /**
     * @return Json
     */
    public function status(): Json
    {
        return new Json( $this->client()
                              ->info() );
    }

    /**
     * @param string      $collection
     * @param string|null $database
     *
     * @return Collection
     */
    public function collection( string $collection, string $database = null ): Collection
    {
        return $this->database( $database )
                    ->collection( $collection );
    }

    /**
     * @param string|null $database
     *
     * @return Database
     */
    public function database( string $database = null ): Database
    {
        return new Database( $this, $database ?? $this->database );
    }
}