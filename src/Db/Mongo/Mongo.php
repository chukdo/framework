<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\Query;
use MongoDB\Driver\Exception\Exception;

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
        $this->dsn     = $dsn;
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
     * @return bool
     */
    public function ping(): bool
    {
        try {
            $command = new Command([ 'ping' => 1 ]);
            $query   = new Json($this->manager->executeCommand('admin', $command));
            $first   = $query->getIndex(0, new Json());
            $ok      = $first->offsetGet('ok');

            if ( $ok == 1 ) {
                return true;
            }
        } catch ( Exception $e ) {
        }

        return false;
    }

    /**
     * @return Json
     */
    public function databases(): Json
    {
        $list    = new Json();

        try {
            $command = new Command([ 'listDatabases' => 1 ]);
            $query   = new Json($this->manager->executeCommand('admin', $command));
            $first   = $query->getIndex(0, new Json());

            $databases = $first->offsetGet('databases', new Json());

            foreach ( $databases as $database ) {
                $list->offsetSet($database->offsetGet('name'), $database->offsetGet('sizeOnDisk'));
            }
        } catch ( Exception $e ) {
        }

        return $list;
    }

    /**
     * @param string $database
     * @return Database
     */
    public function database( string $database ): Database
    {
        return new Database($this, $database);
    }

    /**
     * @param string $database
     * @param string $collection
     * @return Collection
     */
    public function collection( string $database, string $collection ): Collection
    {
        return new Collection($this, $database, $collection);
    }
}