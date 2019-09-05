<?php

Namespace Chukdo\DB\Elastic;

use Elasticsearch\ClientBuilder;

/**
 * Mongo Mongo.
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
     * @var Manager
     */
    protected $elastic;

    /**
     * Elastic constructor.
     * @param string $dsn
     * @param bool   $synchronous
     */
    public function __construct( string $dsn, bool $synchronous = true )
    {
        $this->dsn     = $dsn;
        $this->elastic = ClientBuilder::create()
            ->setHosts(explode(',',
                $dsn))
            ->setHandler($synchronous
                ? ClientBuilder::singleHandler()
                : ClientBuilder::multiHandler())
            ->build();
    }

    // collection = indice
        // schema = mapping
}