<?php

namespace Chukdo\Db\Mongo;

/**
 * Mongo Model .
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Model
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
        $this->collection()->index()->drop();
        $this->index();
        $this->collection()->schema()->drop();
        $this->schema();
    }

    /**
     * Création des index
     */
    public function index()
    {

    }

    /**
     * Création des schema de validation des données
     */
    public function schema()
    {

    }
}