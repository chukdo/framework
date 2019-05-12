<?php

Namespace Chukdo\DB\Mongodb;

use MongoDB\Database as MongoDbDatabase;

/**
 * Mongodb Mongodb Database.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Database
{
    /**
     * @var Mongodb
     */
    protected $mongodb;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var MongoDbDatabase
     */
    protected $mongoDbDatabase;

    /**
     * Database constructor.
     * @param Mongodb $mongodb
     * @param string  $database
     */
    public function __construct( Mongodb $mongodb, string $database )
    {
        $this->mongodb         = $mongodb;
        $this->database        = $database;
        $this->mongoDbDatabase = new MongoDbDatabase($mongodb->mongoDbManager(), $database);

    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->mongoDbDatabase->getDatabaseName();
    }

    /**
     * @return Mongodb
     */
    public function mongodb(): Mongodb
    {
        return $this->mongodb;
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return new Collection($this->mongodb(), $this->database, $collection);
    }
}