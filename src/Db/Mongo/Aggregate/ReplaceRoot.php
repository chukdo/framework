<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate ReplaceRoot.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/replaceRoot/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class ReplaceRoot extends Stage
{
    /**
     * @param $expression
     */
    public function set( $expression )
    {
        $this->pipe = [ 'newRoot' => Expression::parseExpression( $expression ) ];
    }
}