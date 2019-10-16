<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Db\Record\RecordList;
use Chukdo\Db\Record\Record;

/**
 * Server Find.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Find extends Where implements FindInterface
{
	/**
	 * @var array
	 */
	protected $projection = [];

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 * @var array
	 */
	protected $link = [];

	/**
	 * @var array
	 */
	protected $sort = [];

	/**
	 * @var int
	 */
	protected $skip = 0;

	/**
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * @var bool
	 */
	protected $hiddenId = false;

	/**
	 * @param string $field
	 * @param bool   $idAsKey
	 *
	 * @return RecordList
	 */
	public function distinct( string $field, bool $idAsKey = false ): RecordList
	{
		$find = $this->collection()
					 ->client()
					 ->search( $this->query( [
						 'body.aggs.' . $field . 's.terms.field' => $field,
					 ] ) );

		$json = ( new Json( $find ) )->wildcard( 'hits.hits.*._source' );

		$recordList = new RecordList( $this->collection(), $json, $idAsKey, $this->hiddenId );

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
		}

		return $recordList;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		$count = $this->collection()
					  ->client()
					  ->count( $this->query() );

		return (int) $count[ 'count' ];
	}

	/**
	 * @param bool $idAsKey
	 *
	 * @return RecordList
	 */
	public function all( bool $idAsKey = false ): RecordList
	{
		$recordList = new RecordList( $this->collection() );

		$recordList->collection()
				   ->client();

		foreach ( $this->cursor() as $key => $value ) {
			if ( $idAsKey ) {
				$recordList->offsetSet( $value->offsetGet( '_id' ), $value );
			} else {
				$recordList->offsetSet( $key, $value );
			}
		}

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
		}

		/** Suppression des ID defini par without */
		if ( $this->hiddenId ) {
			foreach ( $recordList as $key => $value ) {
				$value->offsetUnset( '_id' );
			}
		}

		return $recordList;
	}

	/**
	 * @param string      $field
	 * @param array       $with
	 * @param array       $without
	 * @param string|null $linked
	 *
	 * @return Find
	 */
	public function link( string $field, array $with = [], array $without = [], string $linked = null ): FindInterface
	{
		$link = new Link( $this->collection()
							   ->database(), $field );

		$this->link[] = $link->with( $with )
							 ->without( $without )
							 ->setLinkedName( $linked );

		return $this;
	}

	/**
	 * @param mixed ...$fields
	 *
	 * @return Find
	 */
	public function with( ...$fields ): FindInterface
	{
		$fields = Arr::spreadArgs( $fields );

		foreach ( $fields as $field ) {
			$this->projection[ $field ] = 1;
		}

		return $this;
	}

	/**
	 * @param mixed ...$fields
	 *
	 * @return Find
	 */
	public function without( ...$fields ): FindInterface
	{
		$fields = Arr::spreadArgs( $fields );

		foreach ( $fields as $field ) {
			if ( $field === '_id' ) {
				$this->hiddenId = true;
			} else {
				$this->projection[ $field ] = 0;
			}
		}

		return $this;
	}

	/**
	 * @param string $field
	 * @param string $sort
	 *
	 * @return Find
	 */
	public function sort( string $field, string $sort = 'ASC' ): FindInterface
	{
		$this->sort[ $field ] = $sort === 'asc' || $sort === 'ASC'
			? 1
			: -1;

		return $this;
	}

	/**
	 * @param int $skip
	 *
	 * @return Find
	 */
	public function skip( int $skip ): FindInterface
	{
		$this->skip = $skip;

		return $this;
	}

	/**
	 * @return Record
	 */
	public function one(): Record
	{
		foreach ( $this->limit( 1 )
					   ->cursor() as $key => $record ) {

			/** Suppression des ID defini par without */
			if ( $this->hiddenId ) {
				$record->offsetUnset( '_id' );
			}

			foreach ( $this->link as $link ) {
				$record = $link->hydrate( $record );
			}

			return $record;
		}

		return new Record( $this->collection() );
	}

	/**
	 * @param int $limit
	 *
	 * @return Find
	 */
	public function limit( int $limit ): FindInterface
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @return array
	 */
	public function projection(): array
	{
		/**$projection = [
		 * 'projection'      => $this->projection,
		 * 'noCursorTimeout' => false,
		 * ];
		 *
		 * if ( !empty( $this->sort ) ) {
		 * $projection[ 'sort' ] = $this->sort;
		 * }
		 *
		 * if ( $this->skip > 0 ) {
		 * $projection[ 'skip' ] = $this->skip;
		 * }
		 *
		 * if ( $this->limit > 0 ) {
		 * $projection[ 'limit' ] = $this->limit;
		 * }
		 *
		 * return $projection;*/
	}


}
