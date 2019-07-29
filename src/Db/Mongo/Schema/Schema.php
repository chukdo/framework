<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;

/**
 * Mongo Schema.
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
            ->info()
            ->toArray());
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $schema = [
            'validator'        => [
                '$jsonSchema' => [ 'bsonType' => 'object' ],
            ],
            'validationLevel'  => 'strict',
            'validationAction' => 'error',
        ];

        $save = new Json($this->collection()
            ->database()
            ->database()
            ->modifyCollection($this->collection()
                ->name(), $schema));

        return $save->offsetGet('ok') == 1;
    }

    /**
     * @param string $name
     * @param array  $options
     * @return Property
     */
    public function setProperty( string $name, array $options = []): Property
    {
        return $this->property()->setProperty($name, $options);
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @param bool|null   $required
     * @param array       $options
     * @return Schema
     */
    public function set( string $name, string $type = null, bool $required = null, array $options = []): self
    {
        $property = $this->property()->setProperty($name, $options);

        if ($type) {
            $property->setType($type);
        }

        if ($required) {
            $this->property()->setRequired($name);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $schema = [
            'validator'        => [
                '$jsonSchema' => $this->get(),
            ],
            'validationLevel'  => 'strict',
            'validationAction' => 'error',
        ];

        $save = new Json($this->collection()
            ->database()
            ->database()
            ->modifyCollection($this->collection()
                ->name(), $schema));

        return $save->offsetGet('ok') == 1;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->property()
            ->get();
    }

    /**
     * @return Property
     */
    public function property(): Property
    {
        return $this->property;
    }
}