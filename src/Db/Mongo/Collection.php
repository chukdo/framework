<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\Collection as MongoDbCollection;

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
        $this->collection = new MongoDbCollection($mongo->manager(), $database, $collection);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->collection->getCollectionName();
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        new Database($this->mongo, $this->databaseName());
    }

    /**
     * @return string
     */
    public function databaseName(): string
    {
        return $this->collection->getDatabaseName();
    }

    /**
     * @return Mongo
     */
    public function mongo(): Mongo
    {
        return $this->mongo;
    }
}