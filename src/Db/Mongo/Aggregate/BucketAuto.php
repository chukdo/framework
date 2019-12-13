<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate BucketAuto.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/bucketAuto/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class BucketAuto extends Stage
{
    /**
     * @param $expression
     *
     * @return $this
     */
    public function groupBy( $expression ): self
    {
        $this->pipe[ 'groupBy' ] = Expression::parseExpression( $expression );

        return $this;
    }

    /**
     * @param int $number
     *
     * @return $this
     */
    public function buckets( int $number ): self
    {
        $this->pipe[ 'buckets' ] = $number;

        return $this;
    }

    /**
     * @param $value
     *
     * @return $this
     */
    public function granularity( string $value ): self
    {
        $this->pipe[ 'granularity' ] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @param        $expression
     *
     * @return $this
     */
    public function output( string $name, $expression ): self
    {
        $this->pipe[ 'output' ][ $name ] = Expression::parseExpression( $expression );

        return $this;
    }
}