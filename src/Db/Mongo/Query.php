<?php

Namespace Chukdo\DB\Mongo;

/**
 * Query Builder.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Query
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * Query constructor.
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function where(): self
    {

    }

    public function get()
    {

    }

    public function increment(): int
    {

    }

    public function decrement(): int
    {

    }

    public function insert(): int
    {

    }

    public function insertGetId(): string
    {

    }

    public function update(): int
    {

    }

    public function delete(): int
    {

    }
}