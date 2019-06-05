<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Helper\Str;
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
     * @param string   $field db._collection ou _collection = _id of collection
     */
    public function __construct( Database $database, string $field )
    {
        $this->database = $database;
        $dbName = $database->name();

        list($db, $field) = array_pad(explode('.', $field), -2, $dbName);

        if ($db != $dbName) {
            $this->database   = $database->mongo()
                ->database($db);
        }

        if (!Str::match('/^_[a-z]+$/i', $field)) {
            throw new MongoException('');
        }


        if ( count($path) > 1 ) {
            $this->database   = $database->mongo()
                ->database($path[ 0 ]);
            $this->collection = substr($path[ 1 ], 1);
            $this->field      = $path[ 1 ];
        }
        else {
            $this->collection = substr($field, 1);
            $this->field      = $field;
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
    public function hydrate( Json $json ): Json
    {
        // loop recursif
        // recherche field => find->all() => map
    }
}