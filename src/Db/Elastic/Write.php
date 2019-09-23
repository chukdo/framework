<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Json\Json;

/**
 * Server Write.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write implements WriteInterface
{
    /**
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @var Json
     */
    protected $fields;

    /**
     * Write constructor.
     *
     * @param CollectionInterface $collection
     */
    public function __construct( CollectionInterface $collection )
    {
        $this->fields     = new Json();
        $this->collection = $collection;
    }

    /**
     * @return Collection
     */
    public function collection(): CollectionInterface
    {
        return $this->collection;
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return $this
     */
    public function where( string $field, string $operator, $value, $value2 = null );

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     *
     * @return $this
     */
    public function orWhere( string $field, string $operator, $value, $value2 = null );

    /**
     * @param iterable $values
     *
     * @return Write
     */
    public function setAll( iterable $values );

    /**
     * @param string $field
     * @param        $value
     *
     * @return Write
     */
    public function set( string $field, $value );

    /**
     * @return int
     */
    public function delete(): int;

    /**
     * @return bool
     */
    public function deleteOne(): bool;

    /**
     * @return JsonInterface
     */
    public function deleteOneAndGet(): JsonInterface;

    /**
     * @return int
     */
    public function update(): int;

    /**
     * @return bool
     */
    public function updateOne(): bool;

    /**
     * @param bool $before
     *
     * @return JsonInterface
     */
    public function updateOneAndGet( bool $before = false ): JsonInterface;

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string;

    /**
     * @return string|null
     */
    public function insert(): ?string;
}