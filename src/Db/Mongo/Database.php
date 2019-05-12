<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\Database as MongoDbDatabase;

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
     * @var string
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
        $this->database = new MongoDbDatabase($mongo->manager(), $database);
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return new Collection($this->mongo(), $this->name(), $collection);
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
        return $this->database->getDatabaseName();
    }
}