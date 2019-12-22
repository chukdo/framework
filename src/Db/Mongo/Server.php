<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;
use Chukdo\Contracts\Db\Server as ServerInterface;

/**
 * Server Server.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Server implements ServerInterface
{
    /**
     * @var string|null
     */
    protected ?string $dsn = null;

    /**
     * @var Manager
     */
    protected Manager $client;

    /**
     * @var string|null
     */
    protected ?string $database;

    /**
     * Server constructor.
     *
     * @param string|null $dsn
     * @param string|null $database
     */
    public function __construct( string $dsn = null, string $database = null )
    {
        $this->dsn      = $dsn
            ?: 'mongodb://127.0.0.1:27017';
        $this->client   = new Manager( $dsn );
        $this->database = $database;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'Mongo';
    }

    /**
     * @param string $name
     * @param array  $hosts array of hosts
     *
     * @return bool
     */
    public function ReplicatSetInitiate( string $name, array $hosts ): bool
    {
        $members = [];
        foreach ( $hosts as $index => $host ) {
            $members[] = [ '_id'  => $index,
                           'host' => $host, ];
        }

        return $this->command( [ 'replSetInitiate' => [ '_id'     => $name,
                                                        'members' => $members, ], ] )
                    ->get( 'ok' ) === 1;
    }

    /**
     * @param array       $args
     * @param string|null $db
     *
     * @return Json
     */
    public function command( array $args, string $db = null ): Json
    {
        try {
            return new Json( $this->client()
                                  ->executeCommand( $db ?? 'admin', new Command( $args ) ) );
        } catch ( Exception $e ) {
        }

        return new Json();
    }

    /**
     * @return Manager
     */
    public function client(): Manager
    {
        return $this->client;
    }

    /**
     * @param string      $collection
     * @param string|null $database
     *
     * @return Collection
     */
    public function collection( string $collection, string $database = null ): Collection
    {
        return $this->database( $database )
                    ->collection( $collection );
    }

    /**
     * @param string|null $database
     *
     * @return Database
     */
    public function database( string $database = null ): Database
    {
        return new Database( $this, $database
            ?: $this->database );
    }

    /**
     * @return Json
     */
    public function databases(): Json
    {
        return $this->command( [ 'listDatabases' => 1 ] )
                    ->wildcard( '0.databases.*.name' );
    }

    /**
     * @return bool
     */
    public function ping(): bool
    {
        return $this->command( [ 'ping' => 1 ] )
                    ->get( '0.ok' ) === 1;
    }

    /**
     * @return Json
     */
    public function status(): Json
    {
        $status = $this->command( [ 'serverStatus' => 1 ] )
                       ->getIndexJson( '0' )
                       ->filter( fn( $k, $v ) => is_scalar( $v )
                           ? $v
                           : false )
                       ->clean();

        return $status;
    }

    /**
     * @return string|null
     */
    public function version(): ?string
    {
        return $this->command( [ 'buildInfo' => 1 ] )
                    ->get( '0.version' );
    }

    /**
     * @return Json
     */
    public function ReplicatSetStatus(): Json
    {
        $status = $this->command( [ 'replSetGetStatus' => 1 ] )
                       ->getIndexJson( '0' )
                       ->filter( fn( $k, $v ) => is_scalar( $v )
                           ? $v
                           : false )
                       ->clean();

        return $status;
    }

    /**
     * @param int $op
     *
     * @return bool
     */
    public function kill( int $op ): bool
    {
        return $this->command( [ 'killOp' => 1,
                                 'op'     => $op, ] )
                    ->get( 'ok' ) === 1;
    }
}