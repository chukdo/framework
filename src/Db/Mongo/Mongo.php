<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\Driver\Manager;

/**
 * Mongo Mongo.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Mongo
{
    /**
     * @var string|null
     */
    protected $dsn = null;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * Mongo constructor.
     * @param string|null $dsn
     */
    public function __construct( string $dsn = null )
    {
        $this->dsn            = $dsn;
        $this->manager = new Manager($dsn);
    }

    /**
     * @return Manager
     */
    public function manager(): Manager
    {
        return $this->manager;
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