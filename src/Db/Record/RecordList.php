<?php

namespace Chukdo\Db\Record;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Json\Json;

/**
 * Server RecordList.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class RecordList extends Json
{
    /**
     * @var CollectionInterface
     */
    protected CollectionInterface $collection;

    /**
     * @var bool
     */
    protected bool $idAsKey = false;

    /**
     * RecordList constructor.
     *
     * @param CollectionInterface $collection
     * @param JsonInterface       $json
     * @param bool                $idAsKey
     */
    public function __construct( CollectionInterface $collection, JsonInterface $json, bool $idAsKey = false )
    {
        parent::__construct( [], false );

        $this->collection = $collection;
        $this->idAsKey    = $idAsKey;

        foreach ( $json as $k => $v ) {
            if ( $idAsKey ) {
                $this->offsetSet( (string) $v->offsetGet( '_id' ), $v );
            }
            else {
                $this->append( $v );
            }
        }
    }

    /**
     * @param mixed $key
     * @param mixed $value
     *
     * @return $this
     */
    public function offsetSet( $key, $value ): self
    {
        parent::offsetSet( $key, $this->collection()
                                      ->record( $value ) );

        return $this;
    }

    /**
     * @return CollectionInterface
     */
    public function collection(): CollectionInterface
    {
        return $this->collection;
    }
}