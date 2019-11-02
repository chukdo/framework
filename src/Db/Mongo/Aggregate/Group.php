<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Arr;

/**
 * Server Aggregate Group.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/group/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Group extends Stage
{
    /**
     * @param $expression
     *
     * @return $this
     */
    public function id( $expression ): self
    {
        $this->pipe[ '_id' ] = Expression::parseExpression( $expression );

        return $this;
    }

    /**
     * @param string $field
     * @param        $expression
     *
     * @return $this
     */
    public function field( string $field, $expression ): self
    {
        $this->pipe[ $field ] = Expression::parseExpression( $expression );

        return $this;
    }
}