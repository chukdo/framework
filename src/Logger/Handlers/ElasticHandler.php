<?php

namespace Chukdo\Logger\Handlers;

use Chukdo\Logger\Formatters\NullFormatter;
use Elasticsearch\ClientBuilder;

/**
 * Gestionnaire des logs pour fichier.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class ElasticHandler extends AbstractHandler
{
    /**
     * @var \Elasticsearch\ClientBuilder
     */
    protected $elastic;

    /**
     * @var string
     */
    private $dsn = '';

    /**
     * ElasticHandler constructor.
     * @param string|null $dsn
     */
    public function __construct( ?string $dsn )
    {
        $this->dsn     = $dsn;
        $this->elastic = ClientBuilder::create()
            ->setHosts(explode(',',
                $dsn))
            ->build();

        $this->setFormatter(new NullFormatter());

        parent::__construct();
    }

    public function __destruct()
    {
        $this->dsn     = null;
        $this->elastic = null;
    }

    /**
     * @param array $record
     * @return bool
     */
    public function write( $record ): bool
    {
        $this->init($record[ 'channel' ]);

        $write = $this->elastic->index([
            'index' => $record[ 'channel' ],
            'type'  => 'search',
            'id'    => uniqid('',
                true),
            'body'  => $record,
        ]);

        return !isset($write[ 'error' ]);
    }

    /**
     * @param string $channel
     */
    protected function init( string $channel ): void
    {
        if( !$this->elastic->indices()
            ->exists([
                'index' => $channel,
            ]) ) {
            $this->elastic->indices()
                ->create([
                    'index' => $channel,
                    'body'  => [
                        'mappings' => [
                            'search' => [
                                'properties' => [
                                    'date' => [
                                        'type'   => 'date',
                                        'format' => 'epoch_second',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]);
        }
    }
}
