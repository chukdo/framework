<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\DB\Mongo\Collection;
use Chukdo\Json\Json;

/**
 * Mongo Schema validation.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema extends Property
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Schema constructor.
     * @param Collection  $collection
     * @param array       $property
     * @param string|null $name
     */
    public function __construct( Collection $collection, Array $property = [], string $name = null )
    {
        $this->collection = $collection;

        parent::__construct($property, $name);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $s = new Json($this->collection->database()
            ->database()
            ->modifyCollection($this->collection->name(), $this->get()));

        return $s->offsetGet('ok') == 1;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return [
            'validator'        => [
                '$jsonSchema' => parent::get(),
            ],
            'validationLevel'  => 'strict',
            'validationAction' => 'error',
        ];
    }
}