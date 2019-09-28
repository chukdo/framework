<?php

namespace Chukdo\Contracts\Db;

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
	 * @return mixed
	 */
	public function collection();

	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return mixed
	 */
	public function where( string $field, string $operator, $value = null, $value2 = null );

	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return mixed
	 */
	public function orWhere( string $field, string $operator, $value = null, $value2 = null );

	/**
	 * @param iterable $values
	 *
	 * @return mixed
	 */
	public function setAll( iterable $values );

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function set( string $field, $value );

	/**
	 * @param string $field
	 *
	 * @return mixed
	 */
	public function unset( string $field );

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function push( string $field, $value );

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function pull( string $field, $value );

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return mixed
	 */
	public function addToSet( string $field, $value );

	/**
	 * @param string $field
	 * @param int    $value
	 *
	 * @return mixed
	 */
	public function inc( string $field, int $value );

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
	 * @return JsonInterface
	 */
	public function updateOneAndGet(): JsonInterface;

	/**
	 * @return string|null
	 */
	public function updateOrInsert(): ?string;

	/**
	 * @return string|null
	 */
	public function insert(): ?string;
}