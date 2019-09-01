<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;

/**
 * Mongo Record.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $id = null;

    /**
     * Record constructor.
     * @param Collection $collection
     * @param null       $data
     */
    public function __construct( Collection $collection, $data = null )
    {
        parent::__construct($data, false);
        parent::__construct($this->filterRecursive(function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        }), false);

        $this->collection = $collection;
        $this->id         = $this->offsetGet('_id');
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection->collection();
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Record
     */
    public function setId( string $id ): self
    {
        $this->id = $id;

        return $this;
    }
}