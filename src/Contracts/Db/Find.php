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
	public function link( string $field, array $with = [], array $without = [], string $linked = null );

	/**
	 * @param mixed ...$fields
	 *
	 * @return mixed
	 */
	public function with( ...$fields );

	/**
	 * @param mixed ...$fields
	 *
	 * @return mixed
	 */
	public function without( ...$fields );

	/**
	 * @param string $field
	 * @param string $sort
	 *
	 * @return mixed
	 */
	public function sort( string $field, string $sort = 'ASC' );

	/**
	 * @param int $skip
	 *
	 * @return mixed
	 */
	public function skip( int $skip );

	/**
	 * @return Record
	 */
	public function one();

	/**
	 * @param int $limit
	 *
	 * @return mixed
	 */
	public function limit( int $limit );

	/**
	 * @param bool $idAsKey
	 *
	 * @return mixed
	 */
	public function all( bool $idAsKey = false );

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
}