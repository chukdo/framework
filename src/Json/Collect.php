<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\Arr;
use Closure;

/**
 * Manipulation de collection de données.
 * @todo         a implementer : https://laravel.com/docs/5.8/collections
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Collect
{
	/**
	 * @var Json
	 */
	protected $collection;

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
	protected $unwind = [];

	/**
	 * @var array
	 */
	protected $group = [];

	/**
	 * @var array
	 */
	protected $filter = [];

	/**
	 * @var array
	 */
	protected $sort = [];

	/**
	 * @var array
	 */
	protected $filterRecursive = [];

	/**
	 * @var array
	 */
	protected $where = [];

	/**
	 * @var array
	 */
	protected $match = [];

	/**
	 * Collect constructor.
	 */
	public function __construct()
	{
		$this->collection = new Json( null, true );
	}

	/**
	 * @param iterable $datas
	 *
	 * @return $this
	 */
	public function push( Iterable $datas ): self
	{
		foreach ( $datas as $data ) {
			$this->append( $data );
		}

		return $this;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return $this
	 */
	public function append( JsonInterface $data ): self
	{
		if ( ( $data = $this->evalWithout( $data ) ) &&
			( $data = $this->evalWith( $data ) ) &&
			( $data = $this->evalFilter( $data ) ) &&
			( $data = $this->evalFilterRecursive( $data ) ) &&
			( $data = $this->evalWhere( $data ) ) &&
			( $data = $this->evalMatch( $data ) ) ) {

			foreach ( $this->unwindData( $data ) as $unwind ) {
				if ( $this->hasGroup() ) {
					$this->groupData( $data );
				} else {
					$this->collection->append( $unwind );
				}
			}
		}

		return $this;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	protected function evalWithout( JsonInterface $data ): ?JsonInterface
	{
		foreach ( $this->without as $without ) {
			$data->unset( $without );
		}

		return $data->isEmpty()
			? null
			: $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	protected function evalWith( JsonInterface $data ): ?JsonInterface
	{
		if ( Arr::hasContent( $this->with ) ) {
			$tmpData = new Json( null, true );

			foreach ( $this->with as $with ) {
				if ( $get = $data->get( $with ) ) {
					$tmpData->set( $with, $get );
				}
			}

			$data = $tmpData;
		}

		return $data->isEmpty()
			? null
			: $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	protected function evalFilter( JsonInterface $data ): ?JsonInterface
	{
		foreach ( $this->filter as $filter ) {
			$data = $data->filter( $filter );
		}

		return $data->isEmpty()
			? null
			: $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	protected function evalFilterRecursive( JsonInterface $data ): ?JsonInterface
	{
		foreach ( $this->filterRecursive as $filter ) {
			$data = $data->filterRecursive( $filter );
		}

		return $data->isEmpty()
			? null
			: $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	public function evalWhere( JsonInterface $data ): ?JsonInterface
	{
		foreach ( $this->where as $where ) {
			if ( $get = $data->get( $where[ 'field' ] ) ) {
				$closure = $this->evalClosure( $where[ 'operator' ] );

				if ( !$closure( $get, $where[ 'value' ], $where[ 'value2' ] ) ) {
					return null;
				}
			} else {
				return null;
			}
		}

		return $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	public function evalMatch( JsonInterface $data ): ?JsonInterface
	{
		foreach ( $this->where as $where ) {
			if ( $get = $data->get( $where[ 'field' ] ) ) {
				$closure = $this->evalClosure( $where[ 'operator' ] );

				if ( !$closure( $get, $data->get( $where[ 'value' ] ), $data->get( $where[ 'value2' ] ) ) ) {
					return null;
				}
			} else {
				return null;
			}
		}

		return $data;
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface
	 */
	protected function unwindData( JsonInterface $data ): JsonInterface
	{
		if ( $this->hasUnwind() ) {
			foreach ( $this->unwind as $unwind ) {
				$data = $data->unwind( $unwind );
			}

			return $data;
		}

		$default = new Json();
		$default->append( $data );

		return $default;
	}

	/**
	 * @return bool
	 */
	protected function hasGroup(): bool
	{
		return Arr::hasContent( $this->group );
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return JsonInterface|null
	 */
	protected function groupData( JsonInterface $data ): ?JsonInterface
	{

	}

	/**
	 * @param string|Closure $operator
	 *
	 * @return Closure
	 */
	protected function evalClosure( $operator ): Closure
	{
		$closure = null;

		switch ( $operator ) {
			case '=' :
				$closure = static function( $v, $value ) {
					return $v === $value
						? $v
						: null;
				};
				break;
			case '!=' :
				$closure = static function( $v, $value ) {
					return $v !== $value
						? $v
						: null;
				};
				break;
			case '>' :
				$closure = static function( $v, $value ) {
					return $v > $value
						? $v
						: null;
				};
				break;
			case '>=':
				$closure = static function( $v, $value ) {
					return $v >= $value
						? $v
						: null;
				};
				break;
			case '<':
				$closure = static function( $v, $value ) {
					return $v < $value
						? $v
						: null;
				};
				break;
			case '<=':
				$closure = static function( $v, $value ) {
					return $v <= $value
						? $v
						: null;
				};
				break;
			case '<>' :
				$closure = static function( $v, $value, $value2 ) {
					return $v < $value && $v > $value2
						? $v
						: null;
				};
				break;
			case '<=>' :
				$closure = static function( $v, $value, $value2 ) {
					return $v <= $value && $v >= $value2
						? $v
						: null;
				};
				break;
			case 'in':
				$closure = static function( $v, $value ) {
					return Arr::in( $v, (array) $value )
						? $v
						: null;
				};
				break;
			case '!in':
				$closure = static function( $v, $value ) {
					return !Arr::in( $v, (array) $value )
						? $v
						: null;
				};
				break;
			case 'type':
				$closure = static function( $v, $value ) {
					return Str::type( $v ) === $value
						? $v
						: null;
				};
				break;
			case '%':
				$closure = static function( $v, $value, $value2 ) {
					return $v % $value === $value2
						? $v
						: null;
				};
				break;
			case 'size':
				$closure = static function( $v, $value ) {
					return count( (array) $v ) === $value
						? $v
						: null;
				};
				break;
			case 'exist':
				$closure = static function( $v ) {
					return $v
						? $v
						: null;
				};
				break;
			case 'regex':
				$closure = static function( $v, $value, $value2 ) {
					return Str::match( '/' . $value . '/' . ( $value2
							?? 'i' ), $v )
						? $v
						: null;
				};
				break;
			case 'match':
				$closure = static function( $v, $value ) {
					$valid = false;

					foreach ( (array) $value as $valueItem ) {
						if ( Arr::in( $valueItem, (array) $v ) ) {
							$valid = true;
							break;
						}
					}

					return $valid
						? $v
						: null;
				};
				break;
			case 'all':
				$closure = static function( $v, $value ) {
					foreach ( (array) $value as $valueItem ) {
						if ( !Arr::in( $valueItem, (array) $v ) ) {
							return null;
						}
					}

					return $v;
				};
				break;
			default :
				if ( $operator instanceof Closure ) {
					$closure = $operator;
				} else {
					throw new JsonException( sprintf( "Unknown operator [%s]", $operator ) );
				}
		}

		return $closure;
	}

	/**
	 * @return bool
	 */
	protected function hasUnwind(): bool
	{
		return Arr::hasContent( $this->unwind );
	}

	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function group( ...$names ): self
	{
		$this->group = Arr::append( $this->group, Arr::spreadArgs( $names ) );

		return $this;
	}

	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function unwind( ...$names ): self
	{
		$this->unwind = Arr::append( $this->unwind, Arr::spreadArgs( $names ) );

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function values(): JsonInterface
	{
		if ( Arr::hasContent( $this->sort ) ) {
			$this->sortCollection();
		}

		return $this->collection;
	}

	/**
	 *
	 */
	protected function sortCollection(): void
	{
		$data = [];
		$args = [];

		foreach ( $this->collection as $k => $v ) {
			$row = [];

			foreach ( $this->sort as $path => $sort ) {
				$row[ $path ] = $v->get( $path );
			}

			$row [ '__RAW__' ] = $v;
			$data[]            = $row;
		}

		foreach ( $this->sort as $path => $sort ) {
			$args[] = array_column( $data, $path );
			$args[] = $sort;
		}

		$args[] = $data;
		array_multisort( ...$args );

		$json = $this->collection->reset();

		foreach ( end( $args ) as $v ) {
			$json->append( $v[ '__RAW__' ] );
		}
	}

	/**
	 * @param string $path
	 * @param int    $sort
	 *
	 * @return $this
	 */
	public function orderBy( string $path, int $sort = SORT_ASC ): self
	{
		$this->sort[ $path ] = $sort;

		return $this;
	}

	/**
	 * @param Closure $closure
	 *
	 * @return $this
	 */
	public function filter( Closure $closure ): self
	{
		$this->filter[] = $closure;

		return $this;
	}

	/**
	 * @param Closure $closure
	 *
	 * @return $this
	 */
	public function filterRecursive( Closure $closure ): self
	{
		$this->filterRecursive[] = $closure;

		return $this;
	}

	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function with( ...$names ): self
	{
		$this->with = Arr::append( $this->with, Arr::spreadArgs( $names ) );

		return $this;
	}

	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function without( ...$names ): self
	{
		$this->without = Arr::append( $this->without, Arr::spreadArgs( $names ) );

		return $this;
	}

	/**
	 * @param string         $field
	 * @param string|Closure $operator
	 * @param                $value
	 * @param null           $value2
	 *
	 * @return Collect
	 */
	public function where( string $field, $operator, $value, $value2 = null ): Collect
	{
		$this->where[] = [
			'field'    => $field,
			'operator' => $operator,
			'value'    => $value,
			'value2'   => $value2,
		];

		return $this;
	}

	/**
	 * @param string         $field
	 * @param string|Closure $operator
	 * @param                $value
	 * @param null           $value2
	 *
	 * @return Collect
	 */
	public function match( string $field, $operator, $value, $value2 = null ): Collect
	{
		$this->match[] = [
			'field'    => $field,
			'operator' => $operator,
			'value'    => $value,
			'value2'   => $value2,
		];

		return $this;
	}
}