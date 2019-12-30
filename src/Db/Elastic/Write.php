<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\DB\Record\Record;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;

/**
 * Server Write.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write extends Where implements WriteInterface
{
    /**
     * @var Json
     */
    protected Json $fields;

    /**
     * Write constructor.
     *
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        parent::__construct( $collection );
        $this->fields = new Json();
    }

    /**
     * @param iterable $values
     *
     * @return $this
     */
    public function setAll( iterable $values ): self
    {
        foreach ( $values as $field => $value ) {
            $this->set( $field, $value );
        }

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    public function set( string $field, $value ): self
    {
        $this->field( 'set', $field, $value );

        return $this;
    }

    /**
     * @param string $keyword
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    protected function field( string $keyword, string $field, $value ): self
    {
        $this->fields->offsetGetOrSet( $keyword )
                     ->offsetSet( $field, $this->filterValues( $field, $value ) );

        return $this;
    }

    /**
     * @param $field
     * @param $value
     *
     * @return array|mixed
     */
    protected function filterValues( $field, $value )
    {
        if ( Is::iterable( $value ) ) {
            $values = [];
            foreach ( $value as $k => $v ) {
                $values[ $k ] = $this->filterValues( $k, $v );
            }
            $value = $values;
        }
        else {
            $value = Collection::filterIn( $field, $value );
        }

        return $value;
    }

    /**
     * @param string $field
     *
     * @return $this
     */
    public function unset( string $field ): self
    {
        $this->field( 'unset', $field, '' );

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    public function push( string $field, $value ): self
    {
        $this->field( 'push', $field, $value );

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    public function addToSet( string $field, $value ): self
    {
        return $this->field( 'addToSet', $field, $value );
    }

    /**
     * @param string $field
     * @param        $value
     *
     * @return $this
     */
    public function pull( string $field, $value ): self
    {
        $this->field( 'pull', $field, $value );

        return $this;
    }

    /**
     * @param string $field
     * @param int    $value
     *
     * @return $this
     */
    public function inc( string $field, int $value ): self
    {
        $this->field( 'inc', $field, $value );

        return $this;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $command = $this->collection()
                        ->client()
                        ->deleteByQuery( $this->filter() );

        return (int) $command[ 'deleted' ];
    }

    /**
     * @return Record
     */
    public function deleteOneAndGet(): Record
    {
        $get = $this->getOne();
        if ( $get->count() > 0 ) {
            $this->deleteOne();
        }

        return $get;
    }

    /**
     * @return Record
     */
    protected function getOne(): Record
    {
        $find = $this->collection()
                     ->find()
                     ->importFilter( $this->exportFilter() );

        return $find->one();
    }

    /**
     * @return bool
     */
    public function deleteOne(): bool
    {
        $command = $this->collection()
                        ->client()
                        ->deleteByQuery( $this->filter( [ 'size' => 1, ] ) );

        return $command[ 'deleted' ] === 1;
    }

    /**
     * @return int
     */
    public function update(): int
    {
        $command = $this->collection()
                        ->client()
                        ->updateByQuery( $this->filter( [ 'body.script' => $this->validatedUpdateFields(),
                                                          'conflicts'   => 'proceed', ] ) );

        return $command[ 'updated' ];
    }

    /**
     * @return array
     */
    public function validatedUpdateFields(): array
    {
        $source = '';
        $params = [];
        foreach ( $this->fields() as $type => $field ) {
            foreach ( (array) $field as $key => $value ) {
                if ( $key === '_id' ) {
                    continue;
                }
                $hydrate                       = $this->hydrateUpdateFields( $type, $key, $value );
                $source                        .= $hydrate[ 'source' ];
                $params[ $hydrate[ 'param' ] ] = $value instanceof Json
                    ? $value->toArray()
                    : $value;
            }
        }

        return [ 'source' => $source,
                 'params' => $params, ];
    }

    /**
     * @return Json
     */
    public function fields(): Json
    {
        return $this->fields;
    }

    /**
     * @param $type
     * @param $key
     * @param $value
     *
     * @return array
     */
    protected function hydrateUpdateFields( $type, $key, $value ): array
    {
        $source = '';
        $param  = str_replace( '.', '_', $key );
        switch ( $type ) {
            case 'set' :
                $source = 'ctx._source.' . $key . '=params.' . $param . ';';
                break;
            case 'unset' :
                $source = 'ctx._source.remove(\'' . $key . '\');';
                break;
            case 'inc' :
                $source = 'ctx._source.' . $key . '+=params.' . $param . ';';
                break;
            case 'push':
                $source = 'ctx._source.' . $key . '.add(params.' . $param . ');';
                break;
            case 'pull':
                $source = 'if(ctx._source.' . $key . '.indexOf(params.' . $param . ') >= 0) {ctx._source.' . $key . '.remove(ctx._source.' . $key . '.indexOf(params.' . $param . '))} ';
                break;
            case 'addToSet':
                $source = 'if(ctx._source.' . $key . '.contains(params.' . $param . ')) {ctx.op = \'noop\'} else {ctx._source.' . $key . '.add(params.' . $param . ')} ';
                break;
            default:
                $source = 'ctx.op = \'none\'';
        }

        return [ 'source' => $source,
                 'param'  => $param, ];
    }

    /**
     * @return Record
     */
    public function updateOneAndGet(): Record
    {
        $get = $this->getOne();
        $this->updateOne();

        return $get;
    }

    /**
     * @return bool
     */
    public function updateOne(): bool
    {
        $command = $this->collection()
                        ->client()
                        ->update( $this->filter( [ 'id'                => $this->getOne()
                                                                               ->id(),
                                                   'body.script'       => $this->validatedUpdateFields(),
                                                   'retry_on_conflict' => 3, ], false ) );

        return $command[ 'result' ] === 'updated';
    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {
        $id = $this->getOne()
                   ->id();
        if ( $id ) {
            $this->updateOne();
        }
        else {
            $id = $this->insert();
        }

        return $id;
    }

    /**
     * @return string
     */
    public function insert(): string
    {
        $body = $this->validatedInsertFields();
        $id   = $body[ '_id' ] ?? $this->collection()
                                       ->id();
        if ( isset( $body[ '_id' ] ) ) {
            unset( $body[ '_id' ] );
        }
        $this->collection()
             ->client()
             ->index( $this->filter( [ 'id'   => $id,
                                       'body' => $body, ], false ) );

        return $id;
    }

    /**
     * @return array
     */
    public function validatedInsertFields(): array
    {
        return $this->fields()
                    ->getJson( 'set' )
                    ->toArray();
    }

    /**
     * @return $this
     */
    public function resetFields(): self
    {
        $this->fields = new Json();

        return $this;
    }

    /**
     * @return $this
     */
    public function resetWhere(): self
    {
        $this->where = [];

        return $this;
    }
}