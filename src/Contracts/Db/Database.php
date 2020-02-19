<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface database de donnée.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Database
{
    /**
     * @return mixed
     */
    public function client();

    /**
     * @return Server
     */
    public function server(): Server;

    /**
     * @param bool $ucFirst
     *
     * @return string|null
     */
    public function name( bool $ucFirst = false ): ?string;

    /**
     * @return JsonInterface
     */
    public function info(): JsonInterface;

    /**
     * @return bool
     */
    public function drop(): bool;

    /**
     * @return JsonInterface
     */
    public function collections(): JsonInterface;

    /**
     * @param string $collection
     *
     * @return Collection
     */
    public function collection( string $collection ): Collection;

    /**
     * @param string $collection
     *
     * @return Collection
     */
    public function createCollection( string $collection ): Collection;

    /**
     * @param string $collection
     *
     * @return bool
     */
    public function collectionExist( string $collection ): bool;

    /**
     * @param string $collection
     *
     * @return Database
     */
    public function dropCollection( string $collection ): Database;
}