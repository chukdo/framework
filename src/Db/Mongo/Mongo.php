<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
use Chukdo\Contracts\Json\Json as JsonInterface;

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
    protected $mongo;

    /**
     * @var string|null
     */
    protected $databaseName = null;

    /**
     * Mongo constructor.
     * @param string      $dsn
     * @param string|null $database
     */
    public function __construct( string $dsn, string $database = null )
    {
        $this->dsn          = $dsn;
        $this->mongo        = new Manager($dsn);
        $this->databaseName = $database;
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
     * @return JsonInterface
     */
    public function command( array $command, string $db = 'admin' ): JsonInterface
    {
        try {
            $command = new Command($command);
            $json    = new Json($this->mongoManager()
                ->executeCommand($db, $command));

            return $json;
        } catch ( Exception $e ) {
        }

        return new Json();
    }

    /**
     * @return Manager
     */
    public function mongoManager(): Manager
    {
        return $this->mongo;
    }

    /**
     * @param string $name
     * @param array  $hosts array of hosts
     * @return bool
     */
    public function ReplicatSetInitiate( string $name, array $hosts ): bool
    {
        $members = [];

        foreach ( $hosts as $index => $host ) {
            $members[] = [
                '_id'  => $index,
                'host' => $host,
            ];
        }

        return $this->command([
                'replSetInitiate' => [
                    '_id'     => $name,
                    'members' => $members,
                ],
            ])
                   ->get('ok') == 1;
    }

    /**
     * @return JsonInterface
     */
    public function ReplicatSetStatus(): JsonInterface
    {
        $status = $this->command([ 'replSetGetStatus' => 1 ])
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
     * @return JsonInterface
     */
    public function status(): JsonInterface
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
     * @return JsonInterface
     */
    public function databases(): JsonInterface
    {
        $list      = new Json();
        $databases = $this->command([ 'listDatabases' => 1 ])
            ->get('0.databases');

        foreach ( $databases as $database ) {
            $list->append($database->offsetGet('name'));
        }

        return $list;
    }

    /**
     * @param string      $collection
     * @param string|null $database
     * @return Collection
     */
    public function collection( string $collection, string $database = null ): Collection
    {
        return new Collection($this, $database
            ?: $this->databaseName, $collection);
    }

    /**
     * @param string|null $database
     * @return Database
     */
    public function database( string $database = null ): Database
    {
        return new Database($this, $database
            ?: $this->databaseName);
    }
}