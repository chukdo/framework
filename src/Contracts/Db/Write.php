<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface d'ecriture de données.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Write
{
    /**
     * Write constructor.
     * @param Collection $collection
     */
    public function __construct( CollectionInterface $collection );

    /**
     * @return Collection
     */
    public function collection(): CollectionInterface;

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return $this
     */
    public function where( string $field, string $operator, $value, $value2 = null ): self;

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return $this
     */
    public function orWhere( string $field, string $operator, $value, $value2 = null ): self;

    /**
     * @param iterable $values
     * @return Write
     */
    public function setAll( iterable $values ): self;

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function set( string $field, $value ): self;

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