<?php

namespace Chukdo\Db\Record;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Db\Mongo\RecordException;
use Exception;
use DateTime;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Server Record.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json
{
    /**
     * @var CollectionInterface
     */
    protected CollectionInterface $collection;

    /**
     * @var string|null
     */
    protected ?string $id;

    /**
     * Record constructor.
     *
     * @param CollectionInterface $collection
     * @param null                $data
     */
    public function __construct( CollectionInterface $collection, $data = null )
    {
        $json             = new Json( $data );
        $filtered         = $json->filterRecursive( fn( $k, $v ) => Collection::filterOut( $k, $v ) );
        $this->collection = $collection;
        $this->id         = $filtered->offsetUnset( '_id' );

        parent::__construct( $filtered, false );
    }

    /**
     * @return Record
     */
    public function delete(): Record
    {
        $write = $this->collection()
                      ->write();
        if ( ( $id = $this->id() ) !== null ) {
            $write->where( '_id', '=', $id );
            $write->deleteOne();

            return $this;
        }
        throw new RecordException( 'No ID to delete Record' );
    }

    /**
     * @return CollectionInterface
     */
    public function collection(): CollectionInterface
    {
        return $this->collection;
    }

    /**
     * @return string|null
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $collection
     *
     * @return Record
     * @throws Exception
     */
    public function moveTo( string $collection ): Record
    {
        $write = $this->collection()
                      ->write();
        if ( ( $id = $this->id() ) !== null ) {
            $write->where( '_id', '=', $id );
            $this->collection()
                 ->database()
                 ->collection( $collection )
                 ->write()
                 ->setAll( $write->deleteOneAndGet() )
                 ->set( 'date_archived', new DateTime() )
                 ->insert();

            return $this;
        }
        throw new RecordException( 'No ID to move Record' );
    }

    /**
     * @return Record
     */
    public function save(): Record
    {
        /** Insert */
        if ( $this->id() === null ) {
            $this->insert();
        } /** Update */ else {
            $this->update();
        }

        return $this;
    }

    /**
     * @return Record
     */
    public function insert(): Record
    {
        $write = $this->collection()
                      ->write()
                      ->setAll( $this->record() );
        /** Insert */
        $this->id = $write->insert();

        return $this;
    }

    /**
     * @return Json
     */
    public function record(): Json
    {

        return $this->filterRecursive( fn( $k, $v ) => $v instanceof Record
            ? null
            : $v );
    }

    /**
     * @return Record
     */
    public function update(): Record
    {
        $write = $this->collection()
                      ->write()
                      ->setAll( $this->record() );
        /** Update */
        $write->where( '_id', '=', $this->id() )
              ->updateOne();

        return $this;
    }
}