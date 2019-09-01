<?php

namespace Chukdo\Db\Mongo\Record;

use Chukdo\Db\Mongo\Index;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Collection;
use Chukdo\Contracts\Db\Record as RecordInterface;

/**
 * Mongo Record.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json implements RecordInterface
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
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * Initialise le modele en injectant le schema et les index
     */
    public function init()
    {
        $this->index()->drop();
        $this->createIndex();
        $this->schema()->drop();
        $this->createSchema();
    }

    /**
     * @return Index
     */
    public function index(): Index
    {
        return $this->collection()->index();
    }

    /**
     * Création des index
     */
    public function createIndex()
    {
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->collection()->schema();
    }

    /**
     * Création des schema de validation des données
     */
    public function createSchema()
    {
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
     * @return RecordInterface
     */
    public function setId( string $id ): RecordInterface
    {
        $this->id = $id;

        return $this;
    }

    public function save()
    {
        // basé sur les données en faisant un update or insert maybe ?

    }
}