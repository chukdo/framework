<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;

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
     * @param array       $fields
     * @param bool        $unique
     * @param string|null $name
     * @return string
     */
    public function create( array $fields, bool $unique = false, string $name = null ): string
    {
        return $this->collection()
            ->collection()
            ->createIndex($fields, [
                'name'   => $name,
                'unique' => $unique,
            ]);
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->collection()
            ->collection()
            ->dropIndexes();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function delete( string $name ): bool
    {
        $drop = $this->collection()
            ->collection()
            ->dropIndex($name);

        return $drop[ 'ok' ] == 1;
    }
}
