<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\Arr;
use Closure;
use Iterable;

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

	protected $filters = [ 'where' => [] ];

	/**
	 * @var array
	 */
	protected $with = [];

	/**
	 * @var array
	 */
	protected $without = [];

	/**
	 * Collect constructor.
	 */
	public function __construct()
	{
		$this->collection = new Json( [], true );
	}

	/**
	 * @param JsonInterface $data
	 *
	 * @return $this
	 */
	public function append( JsonInterface $data ): self
	{
		// if ($aggregate = this->aggregrate($data)) {$this->collection->append( $aggregate );}

		// quid groupBy

		$this->collection->append( $data );

		return $this;
	}

	/**
	 * @param iterable $datas
	 *
	 * @return $this
	 */
	public function push( Iterable $datas ): self
	{
		foreach ($datas as $data) {
			$this->append($data);
		}

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
	 * @param string $field
	 * @param string $operator
	 * @param        $fieldValue
	 * @param null   $fieldValue2
	 *
	 * @return Collect
	 */
	public function match( string $field, string $operator, $fieldValue, $fieldValue2 = null ): Collect
	{
		$json    = new json();
		$closure = $this->whereClosure( $operator );

		foreach ( $this->collection as $k => $row ) {
			if ( ( $row instanceof JsonInterface ) && ( $get = $row->get( $field ) ) && $closure( $get, $row->get( $fieldValue ), $row->get( $fieldValue2 ) ) ) {
				$json->offsetSet( $k, $row );
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * @param string|Closure $operator
	 *
	 * @return Closure
	 */
	protected function whereClosure( $operator ): Closure
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
	 * @param string $field
	 *
	 * @return Collect
	 */
	public function group( string $field ): Collect
	{
		$json = new json();

		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				$json->offsetGetOrSet( $row->get( $field ) )
					 ->append( $row );
			}
		}

		$this->collection->reset( $json );

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
		$json    = new json();
		$closure = $this->whereClosure( $operator );

		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				if ( $get = $row->get( $field ) ) {
					if ( $closure( $get, $value, $value2 ) ) {
						$json->offsetSet( $k, $row );
					}
				}
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * @param string $id
	 *
	 * @return Collect
	 */
	public function keyAsId( string $id ): self
	{
		$json = new Json();

		foreach ( $this->collection as $k => $row ) {
			if ( ( $row instanceof JsonInterface ) && $unsetId = $row->unset( $id ) ) {
				$json->set( $unsetId, $row );
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * @param array   $paths
	 * @param string  $field
	 * @param Closure $closure
	 *
	 * @return Collect
	 */
	public function addToSet( array $paths, string $field, Closure $closure ): self
	{
		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				$arr = [];

				foreach ( $paths as $path ) {
					$arr[ $path ] = $row->get( $path );
				}

				$row->set( $field, $closure( $arr ) );
			}
		}

		return $this;
	}

	/**
	 * @param string  $field
	 * @param Closure $closure
	 *
	 * @return Collect
	 */
	public function filterKey( string $field, Closure $closure ): self
	{
		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				$filter = $closure( $row->get( $field ) );

				if ( $filter ) {
					$row->set( $field, $filter );
				} else {
					$row->unset( $field );
				}
			}
		}

		return $this;
	}

	public function unique(): self
	{
		$cache = [];
		$json  = new Json();

		foreach ( $this->collection as $k => $row ) {
			$key = serialize( $row );

			if ( !isset( $cache[ $key ] ) ) {
				$cache[ $key ] = 1;
				$json->append( $row );
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * Applique une fonction a la collection.
	 *
	 * @param Closure $closure
	 *
	 * @return Collect
	 */
	public function filter( Closure $closure ): self
	{
		$json = new Json();

		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				$filter = $row->filter( $closure );

				if ( $filter->count() > 0 ) {
					$json->set( $k, $filter );
				}
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * Applique une fonction a la collection de maniere recursive.
	 *
	 * @param closure $closure
	 *
	 * @return Collect
	 */
	public function filterRecursive( Closure $closure ): self
	{
		$json = new Json();

		foreach ( $this->collection as $k => $row ) {
			if ( $row instanceof JsonInterface ) {
				$filter = $row->filterRecursive( $closure );

				if ( $filter->count() > 0 ) {
					$json->set( $k, $filter );
				}
			}
		}

		$this->collection->reset( $json );

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function values(): JsonInterface
	{
		return $this->collection;
	}

	/**
	 * @param string $path
	 * @param string $sort
	 *
	 * @return Collect
	 */
	public function sort( string $path, string $sort = 'ASC' ): self
	{
		$toSort = [];

		foreach ( $this->collection as $k => $v ) {
			$get = $v->get( $path );

			if ( !Is::scalar( $get ) || Is::null( $get ) ) {
				$get = uniqid( '', true );
			}

			$toSort[ $get ] = [
				'k' => $k,
				'v' => $v,
			];
		}

		if ( $sort === 'ASC' || $sort === 'asc' ) {
			ksort( $toSort );
		} else {
			krsort( $toSort );
		}

		$json = $this->collection->reset();

		foreach ( $toSort as $sorted ) {
			$json->offsetSet( $sorted[ 'k' ], $sorted[ 'v' ] );
		}

		return $this;
	}
}