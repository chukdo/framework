<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface schema des données.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Schema
{
    /**
     * @return Collection
     */
    public function collection();

    /**
     * @param string $name
     * @return mixed
     */
    public function get( string $name );

    /**
     * @param string      $name
     * @param string|null $type
     * @param array       $options
     * @return mixed
     */
    public function set( string $name, string $type = null,  array $options = [] );

    /**
     * @return mixed
     */
    public function property();

    /**
     * @return JsonInterface
     */
    public function properties(): JsonInterface;

    /**
     * @return bool
     */
    public function drop(): bool;

    /**
     * @return bool
     */
    public function save(): bool;

    /**
     * @return array
     */
    public function toArray(): array;
}