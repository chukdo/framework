<?php

Namespace Chukdo\DB\Mongo;

use Closure;
use Chukdo\Json\Json;
use Chukdo\Helper\Is;
use MongoDB\BSON\Regex;
use MongoDB\Collection as MongoDbCollection;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Timestamp;

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
        $this->collection = new MongoDbCollection($mongo->mongo(), $database, $collection);
    }

    /**
     * @return Json
     */
    public function stat(): Json
    {
        $stats = $this->mongo()
            ->command([ 'collStats' => $this->name() ], $this->databaseName())
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
        return $this->collection()
            ->getCollectionName();
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection;
    }

    /**
     * @return string
     */
    public function databaseName(): string
    {
        return $this->collection()
            ->getDatabaseName();
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->collection()
            ->drop();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @return Json
     */
    public function index(): Index
    {
        return new Index($this);
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        new Database($this->mongo(), $this->databaseName());
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename( string $newName ): bool
    {
        $rename = $this->mongo()
            ->command([
                'renameCollection' => $this->databaseName() . '.' . $this->name(),
                'to'               => $this->databaseName() . '.' . $newName,
            ])
            ->offsetGet('ok');

        if ( $rename == 1 ) {
            return true;
        }

        return false;
    }

    /**
     * @return Write
     */
    public function write(): Write
    {
        return new Write($this);
    }

    /**
     * @return Find
     */
    public function find(): Find
    {
        return new Find($this);
    }

    /**
     * @return Closure
     */
    public static function filterOut()
    {
        return function( $field, $value )
        {
            if ( $value instanceof ObjectId ) {
                return $value->__toString();
            }
            elseif ( $value instanceof Timestamp ) {
                return $value->getTimestamp();
            }
            elseif ( $value instanceof UTCDateTime ) {
                return $value->toDateTime();
            }

            return $value;
        };
    }

    /**
     * @return Closure
     */
    public static function filterIn()
    {
        return function( $field, $value )
        {
            if ( $field === '_id' && Is::string($value) ) {
                $value = new ObjectId($value);
            }
            elseif ( $value instanceof DateTime ) {
                $value = new UTCDateTime($value->getTimestamp());
            }
            elseif ( substr($field, 0, 5) === '_date' ) {
                $value = new UTCDateTime((int) $value);
            }

            return $value;
        };
    }
}