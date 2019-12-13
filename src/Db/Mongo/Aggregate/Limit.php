<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Limit.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/limit/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Limit extends Stage
{
    /**
     * @param int $limit
     */
    public function set( int $limit )
    {
        $this->pipe = $limit;
    }
}