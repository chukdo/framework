<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate GraphLookup.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/graphLookup/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class GraphLookup extends Stage
{
    /**
     * @param string $collection
     *
     * @return $this
     */
    public function from( string $collection ): self
    {
        $this->pipe[ 'from' ] = $collection;

        return $this;
    }

    /**
     * @param $expression
     *
     * @return $this
     */
    public function startWith( $expression ): self
    {
        $this->pipe[ 'startWith' ] = Expression::parseExpression( $expression );

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function connectFromField( string $field ): self
    {
        $this->pipe[ 'connectFromField' ] = $field;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function connectToField( string $field ): self
    {
        $this->pipe[ 'connectToField' ] = $field;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function as( string $field ): self
    {
        $this->pipe[ 'as' ] = $field;

        return $this;
    }

    /**
     * @param int $depth
     *
     * @return $this
     */
    public function maxDepth( int $depth ): self
    {
        $this->pipe[ 'maxDepth' ] = $depth;

        return $this;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function depthField( string $field ): self
    {
        $this->pipe[ 'depthField' ] = $field;

        return $this;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return Match
     */
    public function where( string $field, string $operator, $value, $value2 = null ): Match
    {
        $match = new Match();
        $match->where( $field, $operator, $value, $value2 );

        return $this->pipe[ 'restrictSearchWithMatch' ] = $match;
    }
}