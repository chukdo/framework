<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Db\Mongo\MongoException;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;
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
     * @return Json
     */
    public function required(): Json
    {
        return $this->property->required();
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->properties();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $s = new Json($this->collection->database()
            ->database()
            ->modifyCollection($this->collection->name(), $this->schema()));

        return $s->offsetGet('ok') == 1;
    }

    /**
     * @return array
     */
    public function schema(): array
    {
        return [
            'validator'        => [
                '$jsonSchema' => $this->property->schema(),
            ],
            'validationLevel'  => 'strict',
            'validationAction' => 'error',
        ];
    }

    /**
     * @param JsonInterface $json
     * @return JsonInterface
     */
    public function validator(JsonInterface $json): JsonInterface
    {
        $rules = new Validator($this);

        return $rules->validate($json);
    }

    /**
     * @param string $name
     * @return Property
     */
    public function setProperty( string $name ): Property
    {
        return $this->properties()
            ->offsetGetOrSet($name, new Property());
    }

    /**
     * @param string $name
     * @return Schema
     */
    public function unsetProperty( string $name ): self
    {
        $this->properties()
            ->offsetUnset($name);

        return $this;
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