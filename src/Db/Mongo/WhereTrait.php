<?php

namespace Chukdo\Db\Mongo;

/**
 * Mongo Where Trait.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
trait WhereTrait {

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $orWhere = [];

    /**
     * @param string $field
     * @param        $value
     * @param null   $value2
     * @return Find
     */
    public function where( string $field, $value, $value2 = null ): self
    {
        $this->where[$field] = $this->collection->query($field, $value, $value2);

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     * @param null   $value2
     * @return Find
     */
    public function orWhere( string $field, $value, $value2 = null ): self
    {
        $this->orWhere[$field] = $this->collection->query($field, $value, $value2);

        return $this;
    }

    /**
     * @return array
     */
    public function filter(): array
    {
        $filter = [];

        if ( !empty($this->where) ) {
            $filter[ '$and' ] = [$this->where];
        }

        if ( !empty($this->orWhere) ) {
            $filter[ '$or' ] = [$this->orWhere];
        }

        return $filter;
    }
}