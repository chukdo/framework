<?php

namespace Chukdo\Db\Mongo;

/**
 * Class Where.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Abstract Class Where
{
    use TraitWhereOperation;

    /**
     * @var array
     */
    protected array $where = [];

    /**
     * @var array
     */
    protected array $orWhere = [];

    /**
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     * @param mixed  $value2
     *
     * @return $this
     */
    public function where( string $field, string $operator, $value = null, $value2 = null ): self
    {
        $this->where[ $field ] = $this->whereOperator( $field, $operator, $value, $value2 );

        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     * @param mixed  $value2
     *
     * @return $this
     */
    public function orWhere( string $field, string $operator, $value = null, $value2 = null ): self
    {
        $this->orWhere[ $field ] = $this->whereOperator( $field, $operator, $value, $value2 );

        return $this;
    }

    /**
     * @return array
     */
    public function filter(): array
    {
        $filter = [];
        if ( !empty( $this->where ) ) {
            $filter[ '$and' ] = [ $this->where ];
        }
        if ( !empty( $this->orWhere ) ) {
            $filter[ '$or' ] = [ $this->orWhere ];
        }

        return $filter;
    }
}