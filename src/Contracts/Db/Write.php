<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Db\Record\Record;

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
	 * @return Collection
	 */
	public function collection(): Collection;

	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return Write|Find
	 */
	public function where( string $field, string $operator, $value = null, $value2 = null );

	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return Write|Find
	 */
	public function orWhere( string $field, string $operator, $value = null, $value2 = null );

	/**
	 * @param iterable $values
	 *
	 * @return Write
	 */
	public function setAll( iterable $values ): Write;

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function set( string $field, $value ): Write;

	/**
	 * @param string $field
	 *
	 * @return Write
	 */
	public function unset( string $field ): Write;

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function push( string $field, $value ): Write;

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function pull( string $field, $value ): Write;

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function addToSet( string $field, $value ): Write;

	/**
	 * @param string $field
	 * @param int    $value
	 *
	 * @return Write
	 */
	public function inc( string $field, int $value ): Write;

	/**
	 * @return int
	 */
	public function delete(): int;

	/**
	 * @return bool
	 */
	public function deleteOne(): bool;

	/**
	 * @return Record
	 */
	public function deleteOneAndGet(): Record;

	/**
	 * @return int
	 */
	public function update(): int;

	/**
	 * @return bool
	 */
	public function updateOne(): bool;

	/**
	 * @return Record
	 */
	public function updateOneAndGet(): Record;

	/**
	 * @return string|null
	 */
	public function updateOrInsert(): ?string;

	/**
	 * @return string|null
	 */
	public function insert(): ?string;
}