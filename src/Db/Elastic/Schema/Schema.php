<?php

namespace Chukdo\Db\Elastic\Schema;

use Throwable;
use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Elastic\Collection;
use Chukdo\Json\Json;

/**
 * Server Schema.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema implements SchemaInterface
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var Property
     */
    protected $property;

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
        $name             = $collection->fullName();
        $info             = new Json($collection
            ->client()
            ->indices()
            ->getMapping([ 'index' => $name ]));
        $properties       = $info->get($name . '.mappings', new Json())
            ->toArray();


        $this->property = new Property($properties);
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
    public function properties(): JsonInterface
    {
        return $this->property->properties();
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        try {
            $this->collection()
                ->client()
                ->indices()
                ->putMapping([
                    'index' => $this->collection()
                        ->fullName(),
                    'body'  => [],
                ]);

            return true;
        } catch ( Throwable $e ) {
            return false;
        }
    }

    /**
     * @param string $name
     * @return Property|null
     */
    public function get( string $name ): ?Property
    {
        return $this->property()
            ->toArray($name);
    }

    /**
     * @param string $name
     * @param array  $options
     * @return Property
     */
    public function set( string $name, array $options = [] ): Property
    {
        return $this->property()
            ->set($name, $options);
    }

    /**
     * @return Property
     */
    public function property(): Property
    {
        return $this->property;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        return $this->collection()
            ->client()
            ->indices()
            ->putMapping([
                'index' => $this->collection()
                    ->name(),
                'body'  => $this->toArray(),
            ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->property()
            ->toArray();
    }
}