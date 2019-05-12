<?php

Namespace Chukdo\DB\Mongodb;

use MongoDB\Collection as MongoDbCollection;

/**
 * Mongodb Mongodb Collection.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Collection
{
    /**
     * @var Mongodb
     */
    protected $mongodb;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var String
     */
    protected $collection;

    /**
     * @var MongoDbCollection
     */
    protected $mongoDbCollection;

    /**
     * Collection constructor.
     * @param Mongodb $mongodb
     * @param string  $database
     * @param string  $collection
     */
    public function __construct( Mongodb $mongodb, string $database, string $collection )
    {
        $this->mongodb           = $mongodb;
        $this->database          = $database;
        $this->collection        = $collection;
        $this->mongoDbCollection = new MongoDbCollection($mongodb->mongoDbManager(), $database, $collection);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->mongoDbCollection->getCollectionName();
    }

    /**
     * @return Mongodb
     */
    public function mongodb(): Mongodb
    {
        return $this->mongodb;
    }
}