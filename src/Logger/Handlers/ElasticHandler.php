<?php namespace Chukdo\Logger\Handlers;

Use Elasticsearch\ClientBuilder;

/**
 * Gestionnaire des logs pour fichier
 *
 * @package 	Logger
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author 		Domingo Jean-Pierre <jp.domingo@gmail.com>
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
     * @var string
     */
    private $index = '';

    /**
     * ElasticHandler constructor.
     * @param string $dsn
     * @param string $index
     */
    public function __construct(string $dsn, string $index)
    {
        $this->dsn      = $dsn;
        $this->index    = $index;
        $this->elastic  = ClientBuilder::create()
            ->setHosts(explode(',', $dsn))
            ->build();

        parent::__construct();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->dsn      = null;
        $this->elastic  = null;
    }

    /**
     * @param string $record
     * @return bool
     */
    public function write(string $record): bool
    {
        $this->init();
        $this->elastic->index([
            'index' => $this->index,
            'type'  => 'log',
            'id'    => uniqid('', true),
            'body'  => [
                'date'  => time(),
                'data'  => $record
            ]
        ]);
    }

    /**
     *
     */
    protected function init(): void
    {
        if (!$this->elastic->indices()->exists([$this->index])) {
            $this->elastic->indices()->create([
                'index' => $this->index,
                'body'  => [
                    'mappings' => [
                        'search' => [
                            'properties' => [
                                'date' => [
                                    'type' => 'date',
                                    'format' => 'epoch_second',
                                ],
                                'data' => [
                                    'type' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}