<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
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
	protected $with = [];

	/**
	 * @var array
	 */
	protected $without = [];

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
	 * @return int
	 */
	public function count(): int
	{
		$count = $this->collection()
					  ->client()
					  ->count( $this->projection() );

		return (int) $count[ 'count' ];
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	public function projection( array $params = [] ): array
	{
		$projection = [];

		if ( $this->skip ) {
			$projection[ 'from' ] = $this->skip;
		}

		if ( $this->limit ) {
			$projection[ 'size' ] = $this->limit;
		}

		if ( $this->sort ) {
			$projection[ 'sort' ] = $this->sort;
		}

		return array_merge( $projection, $this->filter( $params ) );
	}

	/**
	 * @return Record
	 */
	public function one(): Record
	{
		$find = $this->collection()
					 ->client()
					 ->search( $this->projection( [ 'size' => 1 ] ) );

		return $this->collection()
					->record( $this->hit( $find[ 'hits' ][ 'hits' ][ 0 ] ?? [] ) );
	}

	/**
	 * @param array $hit
	 *
	 * @return array
	 */
	protected function hit( array $hit ): array
	{
		$source = [];

		if ( isset( $hit[ '_id' ], $hit[ '_source' ] ) ) {
			$source          = $hit[ '_source' ];
			$source[ '_id' ] = $hit[ '_id' ];
		}

		foreach ( $this->without as $without ) {
			if ( $without !== '_id' && isset( $source[ $without ] ) ) {
				unset( $source[ $without ] );
			}
		}

		foreach ( $source as $key => $value ) {
			if ( !Arr::in( $key, $this->with ) ) {
				unset( $source[ $key ] );
			}
		}

		return $source;
	}

	/**
	 * @param bool $idAsKey
	 *
	 * @return RecordList
	 */
	public function all( bool $idAsKey = false ): RecordList
	{
		$find = $this->collection()
					 ->client()
					 ->search( $this->projection() );

		$recordList = new RecordList( $this->collection(), $this->hits( $find ), $idAsKey );

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
		}

		return $recordList;
	}

	/**
	 * @param array $find
	 *
	 * @return JsonInterface
	 */
	protected function hits( array $find ): JsonInterface
	{
		$hits = new Json();

		if ( isset( $find[ 'hits' ][ 'hits' ] ) ) {
			foreach ( $find[ 'hits' ][ 'hits' ] as $hit ) {
				$hits->append( $this->hit( $hit ) );
			}
		}

		return $hits;
	}

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
					 ->search( $this->projection( [
						 'body.aggs.' . $field . 's.terms.field' => $field,
					 ] ) );

		$recordList = new RecordList( $this->collection(), $this->hits( $find ), $idAsKey );

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
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
			$this->with[] = $field;
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
			$this->without[] = $field;
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
		$this->sort[] = $field . ':' . strtolower( $sort );

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
	 * @param int $limit
	 *
	 * @return Find
	 */
	public function limit( int $limit ): FindInterface
	{
		$this->limit = $limit;

		return $this;
	}


}
