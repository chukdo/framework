<?php

Namespace Chukdo\DB\Mongo;

use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\Exception;

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
     * @param string $newName
     * @return bool
     */
    public function rename( string $newName ): bool
    {
        try {
            $command = new Command([
                'renameCollection' => $this->databaseName() . '.' . $this->name(),
                'to'               => $this->databaseName() . '.' . $newName,
            ]);
            $query   = new Json($this->mongo->mongo()
                ->executeCommand('admin', $command));
            $ok      = $query->offsetGet('ok');

            if ( $ok == 1 ) {
                return true;
            }
        } catch ( Exception $e ) {
            throw new MongoException($e->getMessage(), $e->getCode(), $e);
        }

        return false;
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
     * @return Field
     */
    public function and( string $name ): Field
    {
        return $this->and[] = $this->field($name);
    }

    /**
     * @param string $name
     * @return Field
     */
    public function field( string $name ): Field
    {
        return new Field($name);
    }

    /**
     * @param string $name
     * @return Field
     */
    public function or( string $name ): Field
    {
        return $this->or[] = $this->field($name);
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
     * @return array
     */
    public function query(): array
    {
        $query = [];
        $and   = array_map(function( Field $query )
        {
            return [ $query->name() => $query->query() ];
        }, $this->and);


        $or = array_map(function( Field $query )
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
     * @param string $field
     * @return Collection
     */
    public function groupBy( string $field ): self
    {
        return $this;
    }

    /**
     * @param array $values
     * @return Collection
     */
    public function set( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return Collection
     */
    public function unset( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return Collection
     */
    public function push( array $values ): self
    {

    }

    /**
     * @return int
     */
    public function count(): int
    {

    }

    /**
     * @param array $values
     * @return int
     */
    public function insert( array $values ): int
    {

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

    }

    public function delete(): int
    {

    }
}