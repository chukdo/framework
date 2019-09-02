<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Exception;

Class Index
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $index;

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @return JsonInterface
     */
    public function get(): JsonInterface
    {
        $indexes = new Json();

        foreach ( $this->collection()
            ->mongoCollection()
            ->listIndexes() as $index ) {
            $indexes->offsetSet($index[ 'name' ], $index[ 'key' ]);
        }

        return $indexes;
    }

    /**
     * @return Collection
     */
    protected function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param string $field
     * @param string $order
     * @param bool   $unique
     * @return Index
     */
    public function set( string $field, string $order = 'desc', bool $unique = false ): self
    {
        $name  = $unique
            ? $field . '_unique'
            : $field;
        $order = $order == 'asc' || $order == 'ASC'
            ? 1
            : -1;

        $this->index[ $field ] = [
            'name'   => $name,
            'order'  => $order,
            'field'  => $field,
            'unique' => $unique,
        ];

        $this->collection()
            ->mongoCollection()
            ->createIndex([ $field => $order ], [
                'unique' => $unique,
                'name'   => $name,
            ]);

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        try {
            foreach ( $this->index as $index ) {
                $this->collection()
                    ->mongoCollection()
                    ->createIndex([ $index[ 'field' ] => $index[ 'order' ] ], [
                        'unique' => $index[ 'unique' ],
                        'name'   => $index[ 'name' ],
                    ]);
            }

            return true;
        } catch ( Exception $e ) {
            return false;
        }

    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        try {
            $this->collection()
                ->mongoCollection()
                ->dropIndexes();

            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function delete( string $name ): bool
    {
        try {
            $this->collection()
                ->mongoCollection()
                ->dropIndex($name);

            return true;
        } catch ( Exception $e ) {
            return false;
        }
    }
}
