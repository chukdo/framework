<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Db\Mongo\Schema\Schema;

/**
 * Mongo Model .
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
abstract Class Model
{
    /**
     * @var Write
     */
    protected $write;

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
        $this->write      = new Write($collection);
        $this->collection = $collection;
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
    public abstract function createIndex();

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
    public abstract function createSchema();
}