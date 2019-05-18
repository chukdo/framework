<?php

Namespace Chukdo\DB\Mongo;

use MongoDB\BSON\Regex;

/**
 * QueryBuilder Field Builder.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class QueryField
{
    /**
     * @var array
     */
    protected $query = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * QueryField constructor.
     * @param string $name
     */
    public function __construct( string $name )
    {
        $this->name = $name;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function eq( $value ): self
    {
        $this->query[ '$eq' ] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function neq( $value ): self
    {
        $this->query[ '$ne' ] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function gt( $value ): self
    {
        $this->query[ '$gt' ] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function gte( $value ): self
    {
        $this->query[ '$gte' ] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function lt( $value ): self
    {
        $this->query[ '$lt' ] = $value;

        return $this;
    }

    /**
     * @param $value
     * @return QueryField
     */
    public function lte( $value ): self
    {
        $this->query[ '$lte' ] = $value;

        return $this;
    }

    /**
     * @param array $in
     * @return QueryField
     */
    public function in( array $in ): self
    {
        $this->query[ '$in' ] = $in;

        return $this;
    }

    /**
     * @param array $in
     * @return QueryField
     */
    public function nin( array $in ): self
    {
        $this->query[ '$nin' ] = $in;

        return $this;
    }

    /**
     * @param bool $exists
     * @return QueryField
     */
    public function exists( bool $exists = true ): self
    {
        $this->query[ '$exists' ] = $exists;

        return $this;
    }

    /**
     * @param string $type
     * @return QueryField
     */
    public function type( string $type ): self
    {
        $this->query[ '$type' ] = $type;

        return $this;
    }

    /**
     * @param int $size
     * @return QueryField
     */
    public function size( int $size ): self
    {
        $this->query[ '$size' ] = $size;

        return $this;
    }

    /**
     * @param int $divisor
     * @param int $remainder
     * @return QueryField
     */
    public function mod( int $divisor, int $remainder ): self
    {
        $this->query[ '$mod' ] = [
            $divisor,
            $remainder,
        ];

        return $this;
    }

    /**
     * @param string $pattern
     * @param string $options
     * @return QueryField
     */
    public function regex( string $pattern, string $options = 'i' ): self
    {
        $this->query[ '$regex' ] = new Regex($pattern, $options);

        return $this;
    }

    /**
     * @param QueryField ...$queryFields
     * @return QueryField
     */
    public function match( QueryField ...$queryFields ): self
    {
        if ( !isset($this->query[ '$elemMatch' ]) ) {
            $this->query[ '$elemMatch' ] = [];
        }

        foreach ( $queryFields as $queryField ) {
            $this->query[ '$elemMatch' ][ $queryField->name() ] = $queryField->query();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function query(): array
    {
        return $this->query;
    }

    /**
     * @param QueryField ...$queryFields
     * @return QueryField
     */
    public function matchAll( QueryField ...$queryFields ): self
    {
        if ( !isset($this->query[ '$all' ]) ) {
            $this->query[ '$all' ] = [];
        }

        foreach ( $queryFields as $queryField ) {
            $this->query[ '$all' ][ $queryField->name() ] = $queryField->query();
        }

        return $this;
    }
}