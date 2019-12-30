<?php

namespace Chukdo\Db\Mongo\Aggregate;

use Chukdo\Helper\Arr;

/**
 * Aggregate Merge.
 * https://docs.mongodb.com/manual/reference/operator/aggregation/merge/
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Merge extends Stage
{
    /**
     * @param string      $collection
     * @param string|null $db
     *
     * @return $this
     */
    public function into( string $collection, string $db = null ): self
    {
        if ( $db ) {
            $this->pipe[ 'into' ] = $collection;
        }
        else {
            $this->pipe[ 'into' ] = [ 'coll' => $collection,
                                      'db'   => $db, ];
        }

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return $this
     */
    public function on( ...$fields ): self
    {
        $args = Arr::spreadArgs( $fields );

        if ( count( $args ) == 1 ) {
            $this->pipe[ 'on' ] = reset( $args );
        }
        else {
            $this->pipe[ 'on' ] = $args;
        }

        return $this;
    }
}