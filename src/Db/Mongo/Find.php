<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Record\Record;
use Chukdo\Db\Mongo\Record\RecordList;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Record as RecordInterface;
use Chukdo\Contracts\Db\RecordList as RecordListInterface;
use MongoDB\Driver\ReadPreference;

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
	 * @var CollectionInterface
	 */
	protected $collection;

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
	 * Where constructor.
	 *
	 * @param CollectionInterface $collection
	 */
	public function __construct( Collection $collection )
	{
		$this->collection = $collection;
	}

	/**
	 * ReadPreference::RP_PRIMARY = 1,
	 * RP_SECONDARY = 2,
	 * RP_PRIMARY_PREFERRED = 5,
	 * RP_SECONDARY_PREFERRED = 6,
	 * RP_NEAREST = 10
	 *
	 * @param int $readPreference
	 *
	 * @return Find
	 */
	public function setReadPreference( int $readPreference ): self
	{
		$this->collection()
			 ->database()
			 ->server()
			 ->client()
			 ->selectServer( new ReadPreference( $readPreference ) );

		return $this;
	}

	/**
	 * @return Collection
	 */
	public function collection(): Collection
	{
		return $this->collection;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return (int) $this->collection()
						  ->client()
						  ->countDocuments( $this->filter() );
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
			if ( $field == '_id' ) {
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
	 * @return Record
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

	/**
	 * @return JsonInterface
	 */
	public function explain(): JsonInterface
	{
		$explain = $this->collection()
						->database()
						->server()
						->command( [
							'explain' => [
								'find'   => $this->collection()
												 ->name(),
								'filter' => $this->filter(),
							],
						] );

		$json = new Json();

		$json->offsetSet( 'queryPlanner', $explain->get( '0.queryPlanner' ) );
		$json->offsetSet( 'executionStats', $explain->get( '0.executionStats' ) );

		return $json;
	}

	/**
	 * @param string $field
	 *
	 * @return JsonInterface
	 */
	public function distinct( string $field ): JsonInterface
	{
		return new Json( $this->collection()
							  ->client()
							  ->distinct( $field, $this->filter() ) );
	}
}
