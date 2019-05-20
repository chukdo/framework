<?php

Namespace Chukdo\DB\Mongo;

use Closure;
use DateTime;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Collection as MongoDbCollection;

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
     * @var array
     */
    protected $and = [];

    /**
     * @var array
     */
    protected $or = [];

    /**
     * @var array
     */
    protected $projection = [];

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $sort = [];

    /**
     * @var int
     */
    protected $skip = 0;

    /**
     * @var int
     */
    protected $limit = 0;

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
     * @return Closure
     */
    public function filterOut()
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
     * @return Collection
     */
    public function reset(): self
    {
        $this->and        = [];
        $this->or         = [];
        $this->projection = [];
        $this->fields     = [];
        $this->sort       = [];
        $this->skip       = 0;
        $this->limit      = 0;

        return $this;
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
     * @param string $name
     * @return Filter
     */
    public function and( string $name ): Filter
    {
        return $this->and[] = $this->filter($name);
    }

    /**
     * @param string $name
     * @return Filter
     */
    public function filter( string $name ): Filter
    {
        return new Filter($name);
    }

    /**
     * @param string $name
     * @return Filter
     */
    public function or( string $name ): Filter
    {
        return $this->or[] = $this->filter($name);
    }

    /**
     * @param $fields
     * @return Collection
     */
    public function with( $fields ): self
    {
        $fields = (array) $fields;

        foreach ( $fields as $field ) {
            $this->projection[ $field ] = 1;
        }

        return $this;
    }

    /**
     * @param $fields
     * @return Collection
     */
    public function without( $fields ): self
    {
        $fields = (array) $fields;

        foreach ( $fields as $field ) {
            $this->projection[ $field ] = -1;
        }

        return $this;
    }

    /**
     * @return Collection
     */
    public function withoutId(): self
    {
        $this->projection[ '_id' ] = 0;

        return $this;
    }

    /**
     * @param string $field
     * @param string $sort
     * @return Collection
     */
    public function sort( string $field, string $sort ): self
    {
        $this->sort[ $field ] = $sort === 'asc' || $sort === 'ASC'
            ? 1
            : -1;

        return $this;
    }

    /**
     * @param int $skip
     * @return Collection
     */
    public function skip( int $skip ): self
    {
        $this->skip = $skip;

        return $this;
    }

    /**
     * @param int $limit
     * @return Collection
     */
    public function limit( int $limit ): self
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param int|null $limit
     * @return Json
     */
    public function all( int $limit = null ): Json
    {
        $json  = new Json([]);
        $index = 0;

        foreach ( $this->get() as $key => $value ) {
            if ( $limit === null || ( $limit !== null && $index < $limit ) ) {
                $json->offsetSet($key, $value);
            }

            $index++;
        }

        return $json;
    }

    /**
     * @return Cursor
     */
    public function get(): Cursor
    {
        return new Cursor($this);
    }

    /**
     * @return Json
     */
    public function one(): Json
    {
        foreach ( $this->get() as $key => $value ) {
            return $value;
        }

        return new Json();
    }

    /**
     * @param array $values
     * @return Collection
     */
    public function setMultiple( array $values ): self
    {
        foreach ( $values as $field => $value ) {
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     * @return Collection
     */
    public function set( string $field, $value ): self
    {
        return $this->field('set', $field, $value);
    }

    /**
     * @param string $keyword
     * @param string $field
     * @param        $value
     * @return Collection
     */
    protected function field( string $keyword, string $field, $value ): self
    {
        $keyword = '$' . $keyword;

        if ( !isset($this->fields[ $keyword ]) ) {
            $this->fields[ $keyword ] = [];
        }

        $this->fields[ $keyword ][ $field ] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     * @return Collection
     */
    public function setOnInsert( string $field, $value ): self
    {
        return $this->field('setOnInsert', $field, $this->filterIn()($field, $value));
    }

    /**
     * @return Closure
     */
    public function filterIn()
    {
        return function( $field, $value )
        {
            if ( $field === '_id' && Is::string($value) ) {
                $value = new ObjectId($value);
            }
            elseif ( $value instanceof DateTime ) {
                $value = new UTCDateTime($value->getTimestamp());
            }

            return $value;
        };
    }

    /**
     * @param string $field
     * @return Collection
     */
    public function unset( string $field ): self
    {
        return $this->field('unset', $field, '');
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Collection
     */
    public function inc( string $field, int $value ): self
    {
        return $this->field('inc', $field, $value);
    }

    /**
     * @param string $field
     * @param        $value
     * @return Collection
     */
    public function min( string $field, $value ): self
    {
        return $this->field('min', $field, $this->filterIn()($field, $value));
    }

    /**
     * @param string $field
     * @param        $value
     * @return Collection
     */
    public function max( string $field, $value ): self
    {
        return $this->field('max', $field, $this->filterIn()($field, $value));
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Collection
     */
    public function mul( string $field, int $value ): self
    {
        return $this->field('mul', $field, $value);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @return Collection
     */
    public function rename( string $oldName, string $newName ): self
    {
        return $this->field('rename', $oldName, $newName);
    }

    /**
     * @return int
     */
    public function count(): int
    {

    }

    /**
     * @param array|null $values
     * @return string|null
     */
    public function insert( array $values = null ): ?string
    {
        $values = $values
            ?: $this->fields('set');

        return (string) $this->collection()
            ->insertOne($values)
            ->getInsertedId();
    }

    /**
     * @param string|null $keyword
     * @return array
     */
    public function fields( string $keyword = null ): array
    {
        if ( $keyword ) {
            return isset($this->fields[ '$' . $keyword ])
                ? $this->fields[ '$' . $keyword ]
                : [];
        }

        return $this->fields;
    }

    /**
     * @param array $values
     * @return string
     */
    public function insertGetId( array $values ): string
    {

    }

    /**
     * @return int
     */
    public function update(): int
    {
        return (int) $this->collection()
            ->updateMany($this->query(), $this->fields())
            ->getModifiedCount();
    }

    /**
     * @return array
     */
    public function query(): array
    {
        $query = [];
        $and   = array_map(function( Filter $query )
        {
            return [ $query->name() => $query->query() ];
        }, $this->and);


        $or = array_map(function( Filter $query )
        {
            return [ $query->name() => $query->query() ];
        }, $this->or);

        if ( !empty($and) ) {
            $query[ '$and' ] = $and;
        }

        if ( !empty($or) ) {
            $query[ '$or' ] = $or;
        }

        return $query;
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        $projection = [
            'projection'      => $this->projection,
            'noCursorTimeout' => false,
        ];

        if ( !empty($this->sort) ) {
            $projection[ 'sort' ] = $this->sort;
        }

        if ( $this->skip > 0 ) {
            $projection[ 'skip' ] = $this->skip;
        }

        if ( $this->limit > 0 ) {
            $projection[ 'limit' ] = $this->limit;
        }

        return $projection;
    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {
        return (string) $this->collection()
            ->updateOne($this->query(), $this->fields(), [ 'upsert' => true ])
            ->getUpsertedId();
    }

    /**
     * @return int
     */
    public function updateOne(): int
    {
        return (int) $this->collection()
            ->updateOne($this->query(), $this->fields())
            ->getModifiedCount();
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return (int) $this->collection()
            ->deleteMany($this->query())
            ->getDeletedCount();
    }

    /**
     * @return int
     */
    public function deleteOne(): int
    {
        return (int) $this->collection()
            ->deleteOne($this->query())
            ->getDeletedCount();
    }
}