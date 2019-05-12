<?php

Namespace Chukdo\DB\Mongodb;

use MongoDB\Driver\Manager as MongoDbManager;
use MongoDB\Driver\Manager;

/**
 * Mongodb Mongodb.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Mongodb
{
    /**
     * @var string|null
     */
    protected $dsn = null;

    /**
     * @var MongoDbManager
     */
    protected $mongoDbManager;

    /**
     * Mongodb constructor.
     * @param string|null $dsn
     */
    public function __construct( string $dsn = null )
    {
        $this->dsn            = $dsn;
        $this->mongoDbManager = new Manager($dsn);
    }

    /**
     * @return MongoDbManager
     */
    public function mongoDbManager(): MongoDbManager
    {
        return $this->mongoDbManager;
    }

    /**
     * @param string $database
     * @return Database
     */
    public function database(string $database): Database
    {
        return new Database($this, $database);
    }
}