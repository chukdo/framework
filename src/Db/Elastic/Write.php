<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;

/**
 * Server Write.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write extends Where implements WriteInterface
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Json
     */
    protected $fields;

    /**
     * Write constructor.
     *
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->fields     = new Json();
        $this->collection = $collection;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return JsonInterface
     */
    public function fields(): JsonInterface
    {
        return $this->fields;
    }

    /**
     * @param iterable $values
     *
     * @return Write
     */
    public function setAll( iterable $values )
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
     * @return Write
     */
    public function set( string $field, $value )
    {
        $this->fields->offsetSet( $field, $this->filterValues( $field, $value ) );

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
        } else {
            $value = Collection::filterIn( $field, $value );
        }

        return $value;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        $command = $this->collection()
                        ->client()
                        ->deleteByQuery( [
                            'index' => $this->collection()
                                            ->fullName(),
                            'body'  => [
                                'query' => [
                                    'bool' => $this->filter(),
                                ],
                            ],
                        ] );

        return (int) $command[ 'deleted' ];
    }

    /**
     * @return bool
     */
    public function deleteOne(): bool
    {
        $query = [
            'index' => $this->collection()
                            ->fullName(),
            'body'  => [
                'query' => [
                    'bool' => $this->filter(),
                ],
            ],
            'size'  => 1,
        ];

        $command = $this->collection()
                        ->client()
                        ->deleteByQuery( $query );

        return $command[ 'deleted' ] == 1;
    }

    /**
     * @return JsonInterface
     */
    public function deleteOneAndGet(): JsonInterface
    {
        $query = [
            'index' => $this->collection()
                            ->fullName(),
            'body'  => [
                'query' => [
                    'bool' => $this->filter(),
                ],
            ],
        ];

        $json = new Json( $this->collection()
                               ->client()
                               ->search( $query ) );
        $get  = $json->get( 'hits.hits.0._source', new Json() );

        if ( $get->count() > 0 ) {
            $this->deleteOne();
        }

        return $get;
    }

    /**
     * @return int
     */
    public function update(): int
    {

    }

    public function updateTest()
    {
        $query   = [
            'index' => $this->collection()
                            ->fullName(),
            'body'  => [
                'query'  => [
                    'bool' => $this->filter(),
                ],
                'script' => [
                    'source' => 'ctx._source.age="789";ctx._source.info.b.h=["1","cd"];ctx._source.info.b.g.add("15");ctx._source.z=params.uf',
                    'params' => [
                        'uf' => [
                            'titi' => 'tutu',
                            'bibi' => 'bubu'
                        ]
                    ]
                ]
            ],
        ];

        $command = $this->collection()
                        ->client()
                        ->updateByQuery( $query );

        echo '<pre>';
        print_r( $command );
        exit;
    }

    /**
     * @return bool
     */
    public function updateOne(): bool
    {

    }

    /**
     * @param bool $before
     *
     * @return JsonInterface
     */
    public function updateOneAndGet( bool $before = false ): JsonInterface
    {

    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {

    }

    /**
     * @return string
     */
    public function insert(): string
    {
        $id = $this->collection()
                   ->id();
        $this->collection()
             ->client()
             ->index( [
                 'index' => $this->collection()
                                 ->fullName(),
                 'id'    => $id,
                 'body'  => $this->fields()
                                 ->toArray(),
             ] );

        return $id;
    }
}