<?php

namespace Chukdo\Db\Elastic\Schema;

use Chukdo\DB\Elastic\Collection;

/**
 * Elastic Schema.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema
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
        $this->property   = new Property($this->collection()
            ->properties()
            ->toArray());
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    public function drop()
    {
        return $this->collection()
            ->indices()
            ->putMapping([
                'index' => $this->collection()
                    ->name(),
                'body'  => [],
            ]);
    }

    /**
     * @param string $name
     * @param array  $options
     * @return Property
     */
    public function setProperty( string $name, array $options = [] ): Property
    {
        return $this->property()
            ->setProperty($name, $options);
    }

    /**
     * @return Property
     */
    public function property(): Property
    {
        return $this->property;
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @param array       $options
     * @return Schema
     */
    public function set( string $name, string $type = null, array $options = [] ): self
    {
        $property = $this->property()
            ->setProperty($name, $options);

        if ( $type ) {
            $property->setType($type);
        }

        return $this;
    }

    public function save()
    {
        return $this->collection()
            ->indices()
            ->putMapping([
                'index' => $this->collection()
                    ->name(),
                'body'  => $this->get(),
            ]);
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->property()
            ->get();
    }
}