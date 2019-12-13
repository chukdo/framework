<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Interface server de donnée.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Server
{
    /**
     * Server constructor.
     *
     * @param string|null $dsn
     * @param string|null $database
     */
    public function __construct( string $dsn = null, string $database = null );

    /**
     * @param array  $args
     * @param string $db
     *
     * @return JsonInterface
     */
    public function command( array $args, string $db = null ): JsonInterface;

    /**
     * @return object
     */
    public function client();

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return bool
     */
    public function ping(): bool;

    /**
     * @return JsonInterface
     */
    public function status(): JsonInterface;

    /**
     * @return string|null
     */
    public function version(): ?string;

    /**
     * @return JsonInterface
     */
    public function databases(): JsonInterface;

    /**
     * @param string      $collection
     * @param string|null $database
     *
     * @return Collection
     */
    public function collection( string $collection, string $database = null ): Collection;

    /**
     * @param string|null $database
     *
     * @return Database
     */
    public function database( string $database = null ): Database;
}