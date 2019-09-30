<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Record as RecordInterface;
use Chukdo\Contracts\Db\RecordList as RecordListInterface;

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
	 *
	 * @return JsonInterface
	 */
	public function distinct( string $field ): JsonInterface
	{
		$distinct = new Json( $this->collection()
								   ->client()
								   ->search( $this->query( [
									   'body.aggs.' . $field . 's.terms.field' => $field,
								   ] ) ) );

		return $distinct->get( 'hits.hits' );
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
	 * @return RecordListInterface
	 */
	public function all( bool $idAsKey = false ): RecordListInterface
	{
		$recordList = new RecordList( $this->collection() );

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
	 * @return Cursor
	 */
	public function cursor(): Cursor
	{
		$options = array_merge( $this->projection(), $this->options );

		return new Cursor( $this->collection(), $this->collection()
													 ->client()
													 ->find( $this->filter(), $options ) );
	}

	/**
	 * @return array
	 */
	public function projection(): array
	{
		$projection = [
			'projection'      => $this->projection,
			'noCursorTimeout' => false,
		];

		if ( !empty( $this->sort ) ) {
			$projection[ 'sort' ] = $this->sort;
		}

		if ( $this->skip > 0 ) {
			$projection[ 'skip' ] = $this->skip;
		}

		if ( $this->limit > 0 ) {
			$projection[ 'limit' ] = $this->limit;
		}

		return $projection;
	}

	/**
	 * @param string      $field
	 * @param array       $with
	 * @param array       $without
	 * @param string|null $linked
	 *
	 * @return Find
	 */
	public function link( string $field, array $with = [], array $without = [], string $linked = null ): self
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
	public function with( ...$fields ): self
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
	public function without( ...$fields ): self
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
	public function sort( string $field, string $sort = 'ASC' ): self
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
	public function skip( int $skip ): self
	{
		$this->skip = $skip;

		return $this;
	}

	/**
	 * @return RecordInterface
	 */
	public function one(): RecordInterface
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
	public function limit( int $limit ): self
	{
		$this->limit = $limit;

		return $this;
	}


}
