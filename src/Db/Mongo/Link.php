<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;

/**
 * Mongo Link .
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Link
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var string
     */
    protected $collection = null;

    /**
     * @var string
     */
    protected $field = null;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $without = [];

    /**
     * Link constructor.
     * @param Database $database
     * @param string   $field
     */
    public function __construct( Database $database, string $field)
    {
        $this->database = $database;

        $path = explode('.', $field);

        if (count($path) > 1) {
            $this->database = $database->mongo()->database($path[0]);
            $this->field = $path[1];
        } else {
            $this->field = $field;
        }
    }

    /**
     * @param array $fields
     * @return Link
     */
    public function with( array $fields = [] ): self
    {
        $this->with = $fields;

        return $this;
    }

    /**
     * @param array $fields
     * @return Link
     */
    public function without( array $fields = [] ): self
    {
        $this->without = $fields;

        return $this;
    }

    /**
     * @param Json $json
     * @return Json
     */
    public function hydrate(Json $json): Json
    {
        // loop recursif
        // recherche field => find->all() => map
    }
}