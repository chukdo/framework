<?php

namespace Chukdo\Db\Mongo;

use MongoDB\BSON\Regex;

/**
 * Mongo Where Trait.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Where
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $orWhere = [];

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return $this
     */
    public function where( string $field, string $operator, $value, $value2 = null ): self
    {
        $this->where[ $field ] = $this->subQuery($field, $operator, $value, $value2);

        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return array
     */
    protected function subQuery( string $field, string $operator, $value, $value2 = null ): array
    {
        switch ( $operator ) {
            case '=' :
                return [ '$eq' => Collection::filterIn()($field, $value) ];
                break;
            case '!=' :
                return [ '$ne' => Collection::filterIn()($field, $value) ];
                break;
            case '>' :
                return [ '$gt' => Collection::filterIn()($field, $value) ];
                break;
            case '>=':
                return [ '$gte' => Collection::filterIn()($field, $value) ];
                break;
            case '<':
                return [ '$lt' => Collection::filterIn()($field, $value) ];
                break;
            case '<=':
                return [ '$lte' => Collection::filterIn()($field, $value) ];
                break;
            case '<>' :
                return [
                    '$gt' => Collection::filterIn()($field, $value),
                    '$lt' => Collection::filterIn()($field, $value2),
                ];
            case '<=>' :
                return [
                    '$gte' => Collection::filterIn()($field, $value),
                    '$lte' => Collection::filterIn()($field, $value2),
                ];
            case 'in':
                $in = [];

                foreach ( $value as $k => $v ) {
                    $in[ $k ] = Collection::filterIn()($field, $v);
                }

                return [ '$in' => $in ];
                break;
            case '!in':
                $nin = [];

                foreach ( $value as $k => $v ) {
                    $nin[ $k ] = Collection::filterIn()($field, $v);
                }

                return [ '$nin' => $nin ];
                break;
            case 'type':
                return [ '$type' => Collection::filterIn()($field, $value) ];
                break;
            case '%':
                return [
                    '$mod' => [
                        $value,
                        $value2,
                    ],
                ];
            case 'size':
                return [ '$size' => $value ];
            case 'exist':
                return [ '$exists' => $value ];
            case 'regex':
                return [
                    '$regex' => new Regex($value, $value2
                        ?: 'i'),
                ];
                break;
            case 'match':
                return [ '$elemMatch' => $value ];
                break;
            case 'all':
                return [ '$all' => $value ];
                break;
            default :
                throw new MongoException(sprintf("Unknown operator [%s]", $operator));

        }
    }

    /**
     * @param string $field
     * @param        $value
     * @param null   $value2
     * @return Find
     */
    public function orWhere( string $field, $value, $value2 = null ): self
    {
        $this->orWhere[ $field ] = $this->subQuery($field, $value, $value2);

        return $this;
    }

    /**
     * @return array
     */
    public function filter(): array
    {
        $filter = [];

        if ( !empty($this->where) ) {
            $filter[ '$and' ] = [ $this->where ];
        }

        if ( !empty($this->orWhere) ) {
            $filter[ '$or' ] = [ $this->orWhere ];
        }

        return $filter;
    }
}