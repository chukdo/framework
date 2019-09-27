<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;

/**
 * Server Write.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write extends Where implements WriteInterface
{
	/**
	 * @var Collection
	 */
	protected $collection;

	/**
	 * @var Json
	 */
	protected $fields;

	/**
	 * Write constructor.
	 *
	 * @param Collection $collection
	 */
	public function __construct( Collection $collection )
	{
		$this->fields     = new Json();
		$this->collection = $collection;
	}

	/**
	 * @return Collection
	 */
	public function collection(): Collection
	{
		return $this->collection;
	}

	/**
	 * @return array
	 */
	public function validatedUpdateFields(): array
	{
		$source = '';
		$params = [];

		foreach ( $this->fields() as $type => $field ) {
			foreach ( (array) $field as $key => $value ) {
				if ( $key == '_id' ) {
					continue;
				}

				$hydrate = $this->hydrateUpdateFields( $type, $key, $value );
				$source  .= $hydrate[ 'source' ];

				$params[ $hydrate[ 'param' ] ] = $value instanceof JsonInterface
					? $value->toArray()
					: $value;
			}
		}

		return [
			'source' => $source,
			'params' => $params,
		];
	}

	/**
	 * @return JsonInterface
	 */
	public function fields(): JsonInterface
	{
		return $this->fields;
	}

	/**
	 * @param $type
	 * @param $key
	 * @param $value
	 *
	 * @return array
	 */
	protected function hydrateUpdateFields( $type, $key, $value ): array
	{
		$source = '';
		$param  = str_replace( '.', '_', $key );

		switch ( $type ) {
			case 'set' :
				$source = 'ctx._source.' . $key . '=params.' . $param . ';';
				break;
			case 'unset' :
				$source = 'ctx._source.remove(\'' . $key . '\');';
				break;
			case 'inc' :
				$source = 'ctx._source.' . $key . '+=params.' . $param . ';';
				break;
			case 'push':
				$source = 'ctx._source.' . $key . '.add(params.' . $param . ');';
				break;
			case 'pull':
				$source = 'if(ctx._source.' . $key . '.indexOf(params.' . $param . ') >= 0) {ctx._source.' . $key . '.remove(ctx._source.' . $key . '.indexOf(params.' . $param . '))} ';
				break;
			case 'addToSet':
				$source = 'if(ctx._source.' . $key . '.contains(params.' . $param . ')) {ctx.op = \'noop\'} else {ctx._source.' . $key . '.add(params.' . $param . ')} ';
				break;
			default:
				$source = 'ctx.op = \'none\'';
		}

		return [
			'source' => $source,
			'param'  => $param,
		];
	}

	/**
	 * @param iterable $values
	 *
	 * @return Write
	 */
	public function setAll( iterable $values )
	{
		foreach ( $values as $field => $value ) {
			$this->set( $field, $value );
		}

		return $this;
	}

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function set( string $field, $value )
	{
		$this->field( 'set', $field, $value );

		return $this;
	}

	/**
	 * @param string $keyword
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	protected function field( string $keyword, string $field, $value ): self
	{
		$this->fields->offsetGetOrSet( $keyword )
					 ->offsetSet( $field, $this->filterValues( $field, $value ) );

		return $this;
	}

	/**
	 * @param $field
	 * @param $value
	 *
	 * @return array|mixed
	 */
	protected function filterValues( $field, $value )
	{
		if ( Is::iterable( $value ) ) {
			$values = [];

			foreach ( $value as $k => $v ) {
				$values[ $k ] = $this->filterValues( $k, $v );
			}

			$value = $values;
		} else {
			$value = Collection::filterIn( $field, $value );
		}

		return $value;
	}

	/**
	 * @param string $field
	 *
	 * @return $this|mixed
	 */
	public function unset( string $field )
	{
		$this->field( 'unset', $field, '' );

		return $this;
	}

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function push( string $field, $value )
	{
		$this->field( 'push', $field, $value );

		return $this;
	}

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function addToSet( string $field, $value ): self
	{
		return $this->field( 'addToSet', $field, $value );
	}

	/**
	 * @param string $field
	 * @param        $value
	 *
	 * @return Write
	 */
	public function pull( string $field, $value )
	{
		$this->field( 'pull', $field, $value );

		return $this;
	}

	/**
	 * @param string $field
	 * @param int    $value
	 *
	 * @return $this|mixed
	 */
	public function inc( string $field, int $value )
	{
		$this->field( 'inc', $field, $value );

		return $this;
	}

	/**
	 * @return int
	 */
	public function delete(): int
	{
		$command = $this->collection()
						->client()
						->deleteByQuery( [
							'index' => $this->collection()
											->fullName(),
							'body'  => [
								'query' => [
									'bool' => $this->filter(),
								],
							],
						] );

		return (int) $command[ 'deleted' ];
	}

	/**
	 * @return bool
	 */
	public function deleteOne(): bool
	{
		$query = [
			'index' => $this->collection()
							->fullName(),
			'body'  => [
				'query' => [
					'bool' => $this->filter(),
				],
			],
			'size'  => 1,
		];

		$command = $this->collection()
						->client()
						->deleteByQuery( $query );

		return $command[ 'deleted' ] == 1;
	}

	/**
	 * @return JsonInterface
	 */
	public function deleteOneAndGet(): JsonInterface
	{
		$get = $this->getOne();

		if ( $get->count() > 0 ) {
			$this->deleteOne();
		}

		return $get;
	}

	/**
	 * @return JsonInterface
	 */
	protected function getOne(): JsonInterface
	{
		$query = [
			'index' => $this->collection()
							->fullName(),
			'body'  => [
				'query' => [
					'bool' => $this->filter(),
				],
			],
		];

		$json   = new Json( $this->collection()
								 ->client()
								 ->search( $query ) );
		$record = $json->get( 'hits.hits.0._source', new Json() )
					   ->filterRecursive( function( $k, $v ) {
						   return Collection::filterOut( $k, $v );
					   } );

		$record->offsetSet( '_id', $json->get( 'hits.hits.0._id' ) );

		return $record;
	}

	/**
	 * @return int
	 */
	public function update(): int
	{
		$query = [
			'index'     => $this->collection()
								->fullName(),
			'body'      => [
				'query'  => [
					'bool' => $this->filter(),
				],
				'script' => $this->validatedUpdateFields(),
			],
			'conflicts' => 'proceed',
		];

		$command = $this->collection()
						->client()
						->updateByQuery( $query );

		return $command[ 'updated' ];
	}


	/**
	 * @return array
	 */
	public function validatedInsertFields(): array
	{
		return (array) $this->fields()
							->offsetGet( 'set' );
	}

	/**
	 * @return bool
	 */
	public function updateOne(): bool
	{
		$query = [
			'index'     => $this->collection()
								->fullName(),
			'body'      => [
				'query'  => [
					'bool' => [
						'must' => [
							'term' => [
								'_id' => $this->getOne()
											  ->offsetGet( '_id' ),
							],
						],
					],
				],
				'script' => $this->validatedUpdateFields(),
			],
			'conflicts' => 'proceed',
		];

		$command = $this->collection()
						->client()
						->updateByQuery( $query );

		return $command[ 'updated' ];
	}

	/**
	 * @param bool $before
	 *
	 * @return JsonInterface
	 */
	public function updateOneAndGet( bool $before = false ): JsonInterface
	{
		if ( $before ) {
			$get = $this->getOne();

			$this->updateOne();
		} else {
			$this->updateOne();

			$get = $this->getOne();
		}

		// ne fonctionne pas !!!!

		return $get;
	}

	/**
	 * @return string|null
	 */
	public function updateOrInsert(): ?string
	{

	}

	/**
	 * @return string
	 */
	public function insert(): string
	{
		$id = $this->collection()
				   ->id();
		$this->collection()
			 ->client()
			 ->index( [
				 'index' => $this->collection()
								 ->fullName(),
				 'id'    => $id,
				 'body'  => $this->validatedInsertFields(),
			 ] );

		return $id;
	}
}