<?php

namespace Chukdo\Contracts\Db;

/**
 * Interface de recherche de données.
 * @version       1.0.0
 * @copyright     licence MIT, Copyright (C) 2019 Domingo
 * @since         08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Find
{
	/**
	 * @return mixed
	 */
	public function collection();

	/**
	 * @param string      $field
	 * @param array       $with
	 * @param array       $without
	 * @param string|null $linked
	 *
	 * @return Find
	 */
	public function link( string $field, array $with = [], array $without = [], string $linked = null ): Find;

	/**
	 * @param mixed ...$fields
	 *
	 * @return Find
	 */
	public function with( ...$fields ): Find;

	/**
	 * @param mixed ...$fields
	 *
	 * @return Find
	 */
	public function without( ...$fields ): Find;

	/**
	 * @param string $field
	 * @param string $sort
	 *
	 * @return Find
	 */
	public function sort( string $field, string $sort = 'ASC' ): Find;

	/**
	 * @param int $skip
	 *
	 * @return Find
	 */
	public function skip( int $skip ): Find;

	/**
	 * @return Find
	 */
	public function one(): Find;

	/**
	 * @param int $limit
	 *
	 * @return Find
	 */
	public function limit( int $limit ): Find;

	/**
	 * @param bool $idAsKey
	 *
	 * @return Find
	 */
	public function all( bool $idAsKey = false ): Find;

	/**
	 * @return int
	 */
	public function count(): int;

	/**
	 * @param string $field
	 * @param string $operator
	 * @param        $value
	 * @param null   $value2
	 *
	 * @return Find
	 */
	public function where( string $field, string $operator, $value, $value2 = null ): Find;

	/**
	 * @param string $field
	 * @param string $operator
	 * @param        $value
	 * @param null   $value2
	 *
	 * @return Find
	 */
	public function orWhere( string $field, string $operator, $value, $value2 = null ): Find;
}