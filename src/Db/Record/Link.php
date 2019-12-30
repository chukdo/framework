<?php

namespace Chukdo\Db\Record;

use Chukdo\Contracts\Db\Database as DatabaseInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Db\Mongo\RecordException;
use Chukdo\Helper\Is;
use Chukdo\Helper\Arr;
use Chukdo\Helper\Str;

/**
 * Server Link .
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Link
{
    /**
     * @var DatabaseInterface
     */
    protected DatabaseInterface $database;

    /**
     * @var CollectionInterface
     */
    protected CollectionInterface $collection;

    /**
     * @var string
     */
    protected string $field;

    /**
     * @var string
     */
    protected ?string $linked;

    /**
     * @var array
     */
    protected array $with = [];

    /**
     * @var array
     */
    protected array $without = [];

    /**
     * Link constructor.
     *
     * @param DatabaseInterface $database
     * @param string            $field
     */
    public function __construct( DatabaseInterface $database, string $field )
    {
        $this->database = $database;
        $dbName         = $database->name();
        [ $db,
          $field, ] = array_pad( explode( '.', $field ), -2, $dbName );
        if ( !Str::match( '/^_[a-z0-9]+$/i', $field ) ) {
            throw new RecordException( sprintf( 'Field [%s] has not a valid format.', $field ) );
        }
        if ( $db !== $dbName ) {
            $this->database = $database->server()
                                       ->database( $db );
        }
        $this->collection = $this->database->collection( substr( $field, 1 ) );
        $this->field      = $field;
    }

    /**
     * @param string|null $linked
     *
     * @return Link
     */
    public function setLinkedName( string $linked = null ): self
    {
        $this->linked = $linked;

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return Link
     */
    public function with( ...$fields ): self
    {
        $this->with = Arr::spreadArgs( $fields );

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return Link
     */
    public function without( ...$fields ): self
    {
        $this->without = Arr::spreadArgs( $fields );

        return $this;
    }

    /**
     * @param JsonInterface $json
     *
     * @return JsonInterface
     */
    public function hydrate( JsonInterface $json ): JsonInterface
    {
        return $this->hydrateIds( $json, $this->findIds( $this->extractIds( $json ) ) );
    }

    /**
     * @param JsonInterface $json
     * @param JsonInterface $find
     *
     * @return JsonInterface
     */
    protected function hydrateIds( JsonInterface $json, JsonInterface $find ): JsonInterface
    {
        foreach ( $json as $key => $value ) {
            if ( $key === $this->field ) {

                /** Multiple ids */
                if ( Is::JsonInterface( $value ) ) {
                    $list = [];
                    foreach ( (array) $value as $id ) {
                        if ( $get = $find->offsetGet( $id ) ) {
                            $list[] = $this->collection->record( $get );
                        }
                    }
                    if ( !empty( $list ) ) {
                        $json->offsetSet( $this->getLinkedName(), $list );
                    }
                }
                /** Single id */
                else {
                    if ( $get = $find->offsetGet( $value ) ) {
                        $json->offsetSet( $this->getLinkedName(), $this->collection->record( $get ) );
                    }
                }
            }
            else {
                if ( Is::JsonInterface( $value ) ) {
                    $this->hydrateIds( $value, $find );
                }
            }
        }

        return $json;
    }

    /**
     * @return string
     */
    public function getLinkedName(): string
    {
        return $this->linked
            ?: 'linked' . $this->field;
    }

    /**
     * @param array $ids
     *
     * @return RecordList
     */
    protected function findIds( array $ids ): RecordList
    {
        $find = $this->collection->Find();

        return $find->with( $this->with )
                    ->without( $this->without )
                    ->where( '_id', 'in', $ids )
                    ->all( true );
    }

    /**
     * @param JsonInterface $json
     *
     * @return array
     */
    protected function extractIds( JsonInterface $json ): array
    {
        $extractIds = [];
        foreach ( $json as $key => $value ) {
            if ( $key === $this->field ) {
                $extractIds = Arr::append( $value, $extractIds, true );
            }
            else {
                if ( Is::JsonInterface( $value ) ) {
                    $extractIds = Arr::push( $extractIds, $this->extractIds( $value ), true );
                }
            }
        }

        return $extractIds;
    }
}