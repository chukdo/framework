<?php

namespace Chukdo\Contracts\Db;

use Chukdo\Db\Record\Record;
use Chukdo\Db\Record\RecordList;

/**
 * Interface de recherche de données.
 *
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
	public function collection(): Collection;
	
	/**
	 * @param string        $field
	 * @param array         $with
	 * @param array         $without
	 * @param string|null   $linked
	 * @param Database|null $database
	 *
	 * @return Find
	 */
	public function link( string $field, array $with = [], array $without = [], string $linked = null,
	                      Database $database = null ): Find;
	
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
	 * @param int    $sort
	 *
	 * @return Find
	 */
	public function sort( string $field, int $sort = SORT_ASC ): Find;
	
	/**
	 * @param int $skip
	 *
	 * @return Find
	 */
	public function skip( int $skip ): Find;
	
	/**
	 * @return Record
	 */
	public function one(): Record;
	
	/**
	 * @param int $limit
	 *
	 * @return Find
	 */
	public function limit( int $limit ): Find;
	
	/**
	 * @param string $field
	 * @param bool   $idAsKey
	 *
	 * @return RecordList
	 */
	public function distinct( string $field, bool $idAsKey = false ): RecordList;
	
	/**
	 * @param bool $idAsKey
	 *
	 * @return RecordList
	 */
	public function all( bool $idAsKey = false ): RecordList;
	
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
	 * @return Find|Write|object
	 */
	public function where( string $field, string $operator, $value, $value2 = null );
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param        $value
	 * @param null   $value2
	 *
	 * @return Find|Write|object
	 */
	public function orWhere( string $field, string $operator, $value, $value2 = null );
}