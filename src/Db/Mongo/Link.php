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
     * @var Mongo
     */
    protected $mongo;

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
     * @param Mongo  $mongo
     * @param string $field
     */
    public function __construct( Mongo $mongo, string $field)
    {
        $this->mongo   = $mongo;
        $this->field   = $field;
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

    }
}