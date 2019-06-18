<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;

/**
 * Mongo Schema validation.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection->collection();
    }

    /**
     * @return Json
     */
    public function info(): Json
    {
        $json =  $this->collection->mongo()->command([
            'listCollections' => 1,
            'filter'          => [ 'name' => $this->collection->name() ],
        ], $this->collection->databaseName());

        return $json->get('0.options.validator');
    }
}