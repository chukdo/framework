<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Record as RecordInterface;
use Chukdo\Db\Mongo\Record\Record;
use Chukdo\Db\Mongo\Aggregate\Aggregate;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Helper\Is;
use MongoDB\Collection as MongoDbCollection;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Timestamp;
use ReflectionClass;
use ReflectionException;

/**
 * Mongo Mongo Collect.
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
     * @var MongoDbCollection
     */
    protected $collection;

    /**
     * Collect constructor.
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
     * @param string|null $field
     * @param             $value
     * @return mixed
     */
    public static function filterOut( ?string $field, $value )
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
    }

    /**
     * @param string|null $field
     * @param             $value
     * @return ObjectId|UTCDateTime
     */
    public static function filterIn( ?string $field, $value )
    {
        if ( $field === '_id' && Is::string($value) ) {
            $value = new ObjectId($value);
        }
        elseif ( $value instanceof DateTime ) {
            $value = new UTCDateTime($value->getTimestamp());
        }
        elseif ( Str::contain($field, 'date') && Is::scalar($value) ) {
            $value = new UTCDateTime((int) $value);
        }

        return $value;
    }

    /**
     * @return JsonInterface
     */
    public function stat(): JsonInterface
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
     * @return string
     */
    public function databaseName(): string
    {
        return $this->collection()
            ->getDatabaseName();
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection;
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return new Schema($this);
    }

    /**
     * @param $data
     * @return RecordInterface
     */
    public function record( $data ): RecordInterface
    {
        try {
            $reflector = new ReflectionClass('\App\Model\\' . $this->name());

            return $reflector->newInstanceArgs([
                $this,
                $data,
            ]);

        } catch ( ReflectionException $e ) {
            return new Record($this, $data);
        }
    }

    /**
     * @return JsonInterface
     */
    public function info(): JsonInterface
    {
        $json = $this->mongo()
            ->command([
                'listCollections' => 1,
                'filter'          => [ 'name' => $this->name() ],
            ], $this->databaseName());

        return $json->get('0.options.validator.$jsonSchema', new Json());
    }

    /**
     * @return Database
     */
    public function database(): Database
    {
        return new Database($this->mongo(), $this->databaseName());
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
     * @return Aggregate
     */
    public function aggregate(): Aggregate
    {
        return new Aggregate($this);
    }

    /**
     * @return Find
     */
    public function find(): Find
    {
        return new Find($this);
    }
}