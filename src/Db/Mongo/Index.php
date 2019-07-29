<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use Exception;

Class Index
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @return Json
     */
    public function indexes(): Json
    {
        $indexes = new Json();

        foreach ( $this->collection()
            ->collection()
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
    public function create( string $field, string $order = 'desc', bool $unique = false ): self
    {
        $name = $unique
            ? $field . '_unique'
            : $field;
        $order = $order == 'asc' || $order == 'ASC'
            ? 1
            : -1;

        $this->collection()
            ->collection()
            ->createIndex([ $field => $order ], [ 'unique' => $unique, 'name' => $name ]);

        return $this;
    }

    /**
     * @return Index
     */
    public function drop(): self
    {
        try {
            $this->collection()
                ->collection()
                ->dropIndexes();
        } catch ( Exception $e ) {
        }

        return $this;
    }

    /**
     * @param string $name
     * @return Index
     */
    public function delete( string $name ): self
    {
        try {
            $this->collection()
                ->collection()
                ->dropIndex($name);
        } catch ( Exception $e ) {
        }

        return $this;
    }
}
