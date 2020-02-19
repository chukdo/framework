<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Json\Json;
use Chukdo\Db\Record\Record;

/**
 * Interface database de donnée.
 *
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Collection
{
    /**
     * @param string|null $field
     * @param             $value
     *
     * @return mixed
     */
    public static function filterOut( ?string $field, $value );

    /**
     * @param string|null $field
     * @param             $value
     *
     * @return mixed
     */
    public static function filterIn( ?string $field, $value );

    /**
     * @return object
     */
    public function client();

    /**
     * @return string
     */
    public function path(): string;

    /**
     * @param bool $ucFirst
     *
     * @return string
     */
    public function name( bool $ucFirst = false ): string;

    /**
     * @return Json
     */
    public function info(): Json;

    /**
     * @return Database
     */
    public function database(): Database;

    /**
     * @return bool
     */
    public function drop(): bool;

    /**
     * @param string      $collection
     * @param string|null $database
     *
     * @return Collection
     */
    public function rename( string $collection, string $database = null ): Collection;

    /**
     * @return Schema
     */
    public function schema(): Schema;

    /**
     * @return Write
     */
    public function write(): Write;

    /**
     * @return Find
     */
    public function find(): Find;

    /**
     * @return mixed
     */
    public function id();

    /**
     * @param $data
     *
     * @return Record
     */
    public function record( $data ): Record;
}