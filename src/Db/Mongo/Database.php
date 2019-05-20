<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
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
        $this->database = new MongoDbDatabase($mongo->mongo(), $database);
    }

    /**
     * @return bool
     */
    public function repair(): bool
    {
        return $this->mongo()
                   ->command([
                       'repairDatabase' => 1,
                   ], $this->name())
                   ->get('0.ok') == 1;
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
        return $this->database()
            ->getDatabaseName();
    }

    /**
     * @return MongoDbDatabase
     */
    public function database(): MongoDbDatabase
    {
        return $this->database;
    }

    /**
     * @return Json
     */
    public function stat(): Json
    {
        $stats = $this->mongo()
            ->command([ 'dbStats' => 1 ], $this->name())
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
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->database()
            ->drop();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @return Json
     */
    public function collections(): Json
    {
        $list = new Json();

        foreach ( $this->database()
            ->listCollections() as $collection ) {
            $list->append($collection->getName());
        }

        return $list;
    }

    /**
     * @param string $collection
     * @return Collection
     */
    public function collection( string $collection ): Collection
    {
        return new Collection($this->mongo(), $this->name(), $collection);
    }
}