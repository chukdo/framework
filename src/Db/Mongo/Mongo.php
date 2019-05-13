<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Helper\Is;
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
        return $this->command([ 'ping' => 1 ])
                   ->get('0.ok') == 1;
    }

    /**
     * @param array  $command
     * @param string $db
     * @return Json
     */
    public function command( array $command, string $db = 'admin' ): Json
    {
        try {
            $command = new Command($command);
            $json    = new Json($this->manager->executeCommand($db, $command));

            return $json;
        } catch ( Exception $e ) {
        }

        return new Json();
    }

    /**
     * @return Json
     */
    public function status(): Json
    {
        $status = $this->command([ 'serverStatus' => 1 ])
            ->getIndex('0', new Json())
            ->filter(function( $k, $v )
            {
                if ( is_scalar($v) ) {
                    return $v;
                }

                return false;
            })
            ->clean();

        return $status;
    }

    /**
     * @return string|null
     */
    public function version(): ?string
    {
        return $this->command([ 'buildInfo' => 1 ])
            ->get('0.version');
    }

    /**
     * @param int $op
     * @return bool
     */
    public function kill( int $op ): bool
    {
        return $this->command([
                'killOp' => 1,
                'op'     => $op,
            ])
                   ->get('ok') == 1;
    }

    /**
     * @return Json
     */
    public function databases(): Json
    {
        $list      = new Json();
        $databases = $this->command([ 'listDatabases' => 1 ])
            ->get('0.databases');

        foreach ( $databases as $database ) {
            $list->offsetSet($database->offsetGet('name'), $database->offsetGet('sizeOnDisk'));
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