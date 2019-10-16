<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Db\Record\Record;

/**
 * Interface database de donnée.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Collection
{
	/**
	 * @return object
	 */
	public function client();

	/**
	 * @return string
	 */
	public function name(): string;

	/**
	 * @return JsonInterface
	 */
	public function info(): JsonInterface;

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
	 * @param      $data
	 * @param bool $hiddenId
	 *
	 * @return Record
	 */
	public function record( $data, bool $hiddenId = false ): Record;

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
}