<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;

/**
 * Mongo Mongo Collection.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Collection
{
    /**
     * @var Mongo
     */
    protected $mongo;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var String
     */
    protected $collection;

    /**
     * Collection constructor.
     * @param Mongo  $mongo
     * @param string $database
     * @param string $collection
     */
    public function __construct( Mongo $mongo, string $database, string $collection )
    {
        $this->mongo      = $mongo;
        $this->database   = $database;
        $this->collection = new MongoDbCollection($mongo->mongo(), $database, $collection);
    }

    /**
     * @return Json
     */
    public function stat(): Json
    {
        $stats = $this->mongo()
            ->command([ 'collStats' => $this->name() ], $this->databaseName())
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
     * @return Mongo
     */
    public function mongo(): Mongo
    {
        return $this->mongo;
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->collection()->getCollectionName();
    }

    /**
     * @return string
     */
    public function databaseName(): string
    {
        return $this->collection()->getDatabaseName();
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename( string $newName ): bool
    {
        try {
            $command = new Command([
                'renameCollection' => $this->databaseName() . '.' . $this->name(),
                'to'               => $this->databaseName() . '.' . $newName,
            ]);
            $query   = new Json($this->mongo->mongo()
                ->executeCommand('admin', $command));
            $ok      = $query->offsetGet('ok');

            if ( $ok == 1 ) {
                return true;
            }
        } catch ( Exception $e ) {
            throw new MongoException($e->getMessage(), $e->getCode(), $e);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->collection()
            ->drop();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @return Json
     */
    public function index(): Index
    {
        return new Index($this);
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        new Database($this->mongo(), $this->databaseName());
    }
}