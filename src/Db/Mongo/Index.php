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
     * @return Collection
     */
    protected function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return Json
     */
    public function indexes(): Json
    {
        $indexes = new Json();

        foreach ($this->collection()->collection()->listIndexes() as $index) {
            $indexes->offsetSet($index['name'], $index['key']);
        }

        return $indexes;
    }

    /**
     * @param string $name
     * @param array  $fields [key => 1 or -1], 1 = ASC, -1 = DESC
     * @param bool   $unique
     * @return bool
     */
    public function add( string $name, array $fields, bool $unique = false ): bool
    {

    }

    /**
     * @param string $name
     * @return bool
     */
    public function drop( string $name ): bool
    {

    }
}
