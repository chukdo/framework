<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;

/**
 * Mongo Schema validation.
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
        $this->property   = new Property($this->info()
            ->toArray());
    }

    /**
     * @return Json
     */
    public function info(): Json
    {
        $json = $this->collection->mongo()
            ->command([
                'listCollections' => 1,
                'filter'          => [ 'name' => $this->collection->name() ],
            ], $this->collection->databaseName());

        return $json->get('0.options.validator.$jsonSchema');
    }

    /**
     * @return array
     */
    public function schema(): array
    {
        return [
            'collMod' => $this->collection->name(),
            'validator' => [
                '$jsonSchema' => $this->property->schema()
            ]
        ];
    }

    /**
     * @param string $name
     * @return Property
     */
    public function setProperty(string $name): Property
    {
        return $this->properties()->offsetGetOrSet($name, new Property());
    }

    /**
     * @param string $name
     * @return Schema
     */
    public function unsetProperty(string $name): self
    {
        $this->properties()->offsetUnset($name);

        return $this;
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->properties();
    }

    /**
     * @return Json
     */
    public function required(): Json
    {
        return $this->property->required();
    }

    /**
     * @return bool|null
     */
    public function locked(): ?bool
    {
        return $this->property->locked();
    }

    /**
     * @param bool $value
     * @return Schema
     */
    public function setLocked( bool $value ): self
    {
        $this->property->setLocked($value);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Schema
     */
    public function setRequired( ...$fields ): self
    {
        $this->property->setRequired($fields);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Schema
     */
    public function unsetRequired( ...$fields ): self
    {
        $this->property->unsetRequired($fields);

        return $this;
    }

    /**
     * @return Schema
     */
    public function resetRequired(): self
    {
        $this->property->resetRequired();

        return $this;
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection->collection();
    }
}