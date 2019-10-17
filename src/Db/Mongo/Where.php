<?php

namespace Chukdo\Db\Mongo;

use MongoDB\BSON\Regex;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Find as FindInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;

/**
 * Server Where.
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
	 * @var array
	 */
	protected $orWhere = [];

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
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return FindInterface|WriteInterface|object
	 */
	public function where( string $field, string $operator, $value = null, $value2 = null )
	{
		$this->where[ $field ] = $this->subQuery( $field, $operator, $value, $value2 );

		return $this;
	}

	/**
	 * @param string $field
	 * @param string $operator
	 * @param null   $value
	 * @param null   $value2
	 *
	 * @return array
	 */
	protected function subQuery( string $field, string $operator, $value = null, $value2 = null ): array
	{
		switch ( $operator ) {
			case '=' :
				return [ '$eq' => Collection::filterIn( $field, $value ) ];
				break;
			case '!=' :
				return [ '$ne' => Collection::filterIn( $field, $value ) ];
				break;
			case '>' :
				return [ '$gt' => Collection::filterIn( $field, $value ) ];
				break;
			case '>=':
				return [ '$gte' => Collection::filterIn( $field, $value ) ];
				break;
			case '<':
				return [ '$lt' => Collection::filterIn( $field, $value ) ];
				break;
			case '<=':
				return [ '$lte' => Collection::filterIn( $field, $value ) ];
				break;
			case '<>' :
				return [
					'$gt' => Collection::filterIn( $field, $value ),
					'$lt' => Collection::filterIn( $field, $value2 ),
				];
				break;
			case '<=>' :
				return [
					'$gte' => Collection::filterIn( $field, $value ),
					'$lte' => Collection::filterIn( $field, $value2 ),
				];
				break;
			case 'in':
				$in = [];

				foreach ( $value as $k => $v ) {
					$in[ $k ] = Collection::filterIn( $field, $v );
				}

				return [ '$in' => $in ];
				break;
			case '!in':
				$nin = [];

				foreach ( $value as $k => $v ) {
					$nin[ $k ] = Collection::filterIn( $field, $v );
				}

				return [ '$nin' => $nin ];
				break;
			case 'type':
				return [ '$type' => Collection::filterIn( $field, $value ) ];
				break;
			case '%':
				return [
					'$mod' => [
						$value,
						$value2,
					],
				];
				break;
			case 'size':
				return [ '$size' => $value ];
				break;
			case 'exists':
				return [ '$exists' => true ];
				break;
			case !'exists':
				return [ '$exists' => false ];
				break;
			case 'regex':
				return [
					'$regex' => new Regex( $value, $value2
						?? 'i' ),
				];
				break;
			case 'match':
				return [ '$elemMatch' => $value ];
				break;
			case 'all':
				return [ '$all' => $value ];
				break;
			default :
				throw new MongoException( sprintf( 'Unknown operator [%s]', $operator ) );

		}
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
		$this->orWhere[ $field ] = $this->subQuery( $field, $operator, $value, $value2 );

		return $this;
	}

	/**
	 * @return array
	 */
	public function filter(): array
	{
		$filter = [];

		if ( !empty( $this->where ) ) {
			$filter[ '$and' ] = [ $this->where ];
		}

		if ( !empty( $this->orWhere ) ) {
			$filter[ '$or' ] = [ $this->orWhere ];
		}

		return $filter;
	}
}