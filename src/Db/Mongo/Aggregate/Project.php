<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Aggregate Project.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/project/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Project extends Stage
{
    /**
     * @param string $field
     * @param bool   $visibility
     *
     * @return $this
     */
    public function set( string $field, bool $visibility ): self
    {
        $this->pipe[ $field ] = (int)$visibility;

        return $this;
    }
}