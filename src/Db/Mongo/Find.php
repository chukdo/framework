<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Helper\Arr;
use Chukdo\Json\Json;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Db\Record\Link;
use Chukdo\Db\Record\Record;
use Chukdo\Db\Record\RecordList;
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
	 * @return RecordList
	 */
	public function all( bool $idAsKey = false ): RecordList
	{
		$options    = Arr::merge( $this->projection(), $this->options );
		$find       = $this->collection()
						   ->client()
						   ->find( $this->filter(), $options );
		$recordList = new RecordList( $this->collection(), new Json( $find ), $idAsKey );

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
		}

		return $recordList;
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
	 * @return FindInterface
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
	 * @return FindInterface
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
	 * @return FindInterface
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
	 * @return FindInterface
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
	 * @return FindInterface
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
		$options = Arr::merge( $this->projection(), $this->options );
		$find    = $this->collection()
						->client()
						->findOne( $this->filter(), $options );

		$record = $this->collection()
					   ->record( $find, $this->hiddenId );

		foreach ( $this->link as $link ) {
			$record = $link->hydrate( $record );
		}

		return $record;
	}

	/**
	 * @param int $limit
	 *
	 * @return FindInterface
	 */
	public function limit( int $limit ): FindInterface
	{
		$this->limit = $limit;

		return $this;
	}

	/**
	 * @param string $field
	 * @param bool   $idAsKey
	 *
	 * @return RecordList
	 */
	public function distinct( string $field, bool $idAsKey = false ): RecordList
	{
		$find       = $this->collection()
						   ->client()
						   ->distinct( $field, $this->filter() );
		$recordList = new RecordList( $this->collection(), new Json( $find ), $idAsKey );

		foreach ( $this->link as $link ) {
			$recordList = $link->hydrate( $recordList );
		}

		return $recordList;
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
}
