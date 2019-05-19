<?php


namespace Chukdo\Db\Mongo;

use MongoDB\Collection as MongoDbCollection;
use MongoDB\Driver\Cursor as MongoDbCursor;

/**
 * Mongodb cursor.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Cursor
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var MongoDbCursor
     */
    protected $cursor;

    /**
     * Cursor constructor.
     * @param QueryBuilder $querybuilder
     */
    public function __construct( QueryBuilder $querybuilder )
    {
        $this->collection = $querybuilder->collection();
        $this->cursor     = $this->collection->find($querybuilder->query(), $querybuilder->projection());
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection;
    }

    /**
     * @return MongoDbCursor
     */
    public function cursor(): MongoDbCursor
    {
        return $this->cursor;
    }
}