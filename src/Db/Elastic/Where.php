<?php

namespace Chukdo\Db\Elastic;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Json\Json;

/**
 * Server Where.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Abstract Class Where
{
	/**
	 * @var Collection
	 */
	protected $collection;
	
	/**
	 * @var array
	 */
	protected $where = [];
	
	/**
	 * Find constructor.
	 *
	 * @param Collection $collection
	 */
	public function __construct( Collection $collection )
	{
		$this->collection = $collection;
	}
	
	/**
	 * @return CollectionInterface|Collection
	 */
	public function collection(): CollectionInterface
	{
		return $this->collection;
	}
	
	/**
	 * @return array
	 */
	public function exportFilter(): array
	{
		return $this->where;
	}
	
	/**
	 * @param array $where
	 *
	 * @return FindInterface|WriteInterface|object
	 */
	public function importFilter( array $where )
	{
		$this->where = $where;
		
		return $this;
	}
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return FindInterface|WriteInterface|object
	 */
	public function where( string $field, string $operator, $value = null, $value2 = null )
	{
		$keyword = 'must';
		switch ( $operator ) {
			case '==' :
				$keyword = 'filter';
				break;
			case '!==' :
			case '!=' :
			case '!in' :
			case '!exists' :
				$keyword = 'must_not';
				break;
		}
		if ( !isset( $this->where[ $keyword ] ) ) {
			$this->where[ $keyword ] = [];
		}
		$this->where[ $keyword ][] = $this->whereOperator( $field, $operator, $value, $value2 );
		
		return $this;
	}
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return FindInterface|WriteInterface|object
	 */
	public function orWhere( string $field, string $operator, $value = null, $value2 = null )
	{
		$keyword = 'should';
		switch ( $operator ) {
			case '==' :
				$keyword = 'filter';
				break;
			case '!==' :
			case '!=' :
			case '!in' :
			case '!exists' :
				$keyword = 'should_not';
				break;
		}
		if ( !isset( $this->where[ $keyword ] ) ) {
			$this->where[ $keyword ] = [];
		}
		$this->where[ $keyword ][] = $this->whereOperator( $field, $operator, $value, $value2 );
		
		return $this;
	}
	
	/**
	 * @param array $params
	 * @param bool  $withFilter
	 *
	 * @return array
	 */
	public function filter( array $params = [], bool $withFilter = true ): array
	{
		$query = new Json( [
			                   'index' => $this->collection()
			                                   ->fullName(),
			                   'body'  => [],
		                   ] );
		foreach ( $params as $key => $value ) {
			$query->set( $key, $value );
		}
		if ( $withFilter && count( $this->where ) > 0 ) {
			$query->set( 'body.query.bool', $this->where );
		}
		
		return $query->toArray();
	}
	
	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return array
	 */
	protected function whereOperator( string $field, string $operator, $value = null, $value2 = null ): array
	{
		switch ( $operator ) {
			case '==' :
			case '!==' :
			case '=' :
			case '!=' :
				return [
					'term' => [
						$field => Collection::filterIn( $field, $value ),
					],
				];
				break;
			case '>' :
				return [
					'range' => [
						$field => [
							'gt' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case '>=':
				return [
					'range' => [
						$field => [
							'gte' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case '<':
				return [
					'range' => [
						$field => [
							'lt' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case '<=':
				return [
					'range' => [
						$field => [
							'lte' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case '<>' :
				return [
					'range' => [
						$field => [
							'gt' => Collection::filterIn( $field, $value ),
							'lt' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case '<=>' :
				return [
					'range' => [
						$field => [
							'gte' => Collection::filterIn( $field, $value ),
							'lte' => Collection::filterIn( $field, $value ),
						],
					],
				];
				break;
			case 'in':
			case '!in':
				$in = [];
				foreach ( $value as $k => $v ) {
					$in[ $k ] = Collection::filterIn( $field, $v );
				}
				
				return [
					'terms' => [
						$field => $in,
					],
				];
				break;
			case 'size':
				return [
					'
                script' => [
						'script' => 'doc[\'' . $field . '\']values.size() = ' . $value,
					],
				];
				break;
			case 'exists':
			case '!exists':
				return [
					'exists' => [
						'field' => $field,
					],
				];
				break;
			case 'regex':
				return [
					'regexp' => [
						$field => [
							'value' => $value,
							'flags' => $value2 ?? 'ALL',
						],
					],
				];
				break;
			case 'match':
				return [
					'match' => [
						$field => $value,
					],
				];
				break;
			default :
				throw new ElasticException( sprintf( "Unknown operator [%s]", $operator ) );
		}
	}
}