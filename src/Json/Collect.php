<?php

namespace Chukdo\Json;

use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\Arr;
use Closure;

/**
 * Manipulation de collection de données.
 *
 * @todo         a implementer : https://laravel.com/docs/5.8/collections
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Collect
{
	/**
	 * @var array
	 */
	protected $collection = [];
	
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
	protected $sum = [];
	
	/**
	 * @var array
	 */
	protected $sumCache = [];
	
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
	 * @param array $data
	 *
	 * @return $this
	 */
	public function append( array $data ): self
	{
		foreach ( $this->unwindData( $data ) as $unwind ) {
			if ( $eval = $this->eval( $unwind ) ) {
				if ( $this->hasGroup() ) {
					$this->groupData( $eval );
				} else {
					$this->appendData( $eval );
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	public function evalWhere( array $data ): ?array
	{
		foreach ( $this->where as $where ) {
			if ( $get = Arr::get( $data, $where[ 'field' ] ) ) {
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
	 * @param array $data
	 *
	 * @return array|null
	 */
	public function evalMatch( array $data ): ?array
	{
		foreach ( $this->where as $where ) {
			if ( $get = Arr::get( $data, $where[ 'field' ] ) ) {
				$closure = $this->evalClosure( $where[ 'operator' ] );
				if ( !$closure( $get, Arr::get( $data, $where[ 'value' ] ), Arr::get( $data, $where[ 'value2' ] ) ) ) {
					return null;
				}
			} else {
				return null;
			}
		}
		
		return $data;
	}
	
	/**
	 * @param string      $field
	 * @param string|null $name
	 * @param string|null $group
	 *
	 * @return $this
	 */
	public function sum( string $field, string $name = null, string $group = null ): self
	{
		$this->sum[ $group ?? uniqid( '', true ) ] = [
			'field' => $field,
			'name'  => $name ?? $field,
		];
		
		return $this;
	}
	
	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function group( ...$names ): self
	{
		$this->group = Arr::push( $this->group, Arr::spreadArgs( $names ) );
		
		return $this;
	}
	
	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function unwind( ...$names ): self
	{
		$this->unwind = Arr::push( $this->unwind, Arr::spreadArgs( $names ) );
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function values(): array
	{
		if ( $this->hasSort() && !$this->hasGroup() ) {
			$this->sortCollection();
		}
		
		return $this->collection;
	}
	
	/**
	 * @param string $path
	 * @param int    $sort
	 *
	 * @return $this
	 */
	public function sort( string $path, int $sort = SORT_ASC ): self
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
		$this->with = Arr::push( $this->with, Arr::spreadArgs( $names ) );
		
		return $this;
	}
	
	/**
	 * @param mixed ...$names
	 *
	 * @return Collect
	 */
	public function without( ...$names ): self
	{
		$this->without = Arr::push( $this->without, Arr::spreadArgs( $names ) );
		
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
	
	/**
	 * @param array $data
	 *
	 * @return iterable
	 */
	protected function unwindData( array $data ): Iterable
	{
		if ( $this->hasUnwind() ) {
			foreach ( $this->unwind as $unwind ) {
				$data = Arr::unwind( $data, $unwind );
			}
			
			return $data;
		}
		
		return [ $data ];
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	protected function eval( array $data ): ?array
	{
		if ( ( $without = $this->evalWithout( $data ) ) && ( $with = $this->evalWith( $without ) ) &&
		     ( $filter = $this->evalFilter( $with ) ) && ( $filterRecursive = $this->evalFilterRecursive( $filter ) ) &&
		     ( $where = $this->evalWhere( $filterRecursive ) ) && ( $match = $this->evalMatch( $where ) ) ) {
			return $match;
		}
		
		return null;
	}
	
	/**
	 * @return bool
	 */
	protected function hasGroup(): bool
	{
		return Arr::hasContent( $this->group );
	}
	
	/**
	 * @param string $group
	 * @param array  $path
	 * @param array  $data
	 *
	 * @return $this
	 */
	protected function sumGroupData( string $group, array $path, array $data): self
	{
		$name    = $this->sum[ $group ][ 'name' ];
		$field   = $this->sum[ $group ][ 'field' ];
		$sumPath = ltrim( implode( '.projection.', $path ) . '.sum.' . $name, '.projection' );
		
		$getField = Arr::get( $data, $field );
		
		if (Arr::isArray($getField)) {
			$getField = count($getField);
		}
		
		if ((int) $getField == $getField) {
			Arr::inc( $this->collection, $sumPath, $getField);
		}
		
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function groupData( array $data ): self
	{
		$path = [];
		$sum  = false;
		
		foreach ( $this->group as $group ) {
			$get = Arr::get( $data, $group );
			
			if ( Is::null( $get ) || !Is::scalar( $get ) ) {
				return $this;
			}
			
			$path[] = $get;
			
			if ( self::hasSum( $group ) ) {
				$sum = true;
				$this->sumGroupData( $group, $path, $data);
			}
		}
		
		if ( !$sum ) {
			$groupPath = implode( '.projection.', $path ) . '.projection';
			
			Arr::addToSet( $this->collection, $groupPath, $data );
		}
		
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function sumData( array $data ): self
	{
		foreach ( $this->sum as $sum ) {
			if ( !isset( $this->collection[ $sum[ 'name' ] ] ) ) {
				$this->collection[ $sum[ 'name' ] ] = 0;
			}
			
			$this->collection[ $sum[ 'name' ] ] += Arr::get( $data, $sum[ 'field' ] );
		}
		
		return $this;
	}
	
	/**
	 * @param array $data
	 *
	 * @return $this
	 */
	protected function appendData( array $data ): self
	{
		if ( $this->hasSum() ) {
			$this->sumData( $data );
		} else {
			$this->collection[] = $data;
		}
		
		return $this;
	}
	
	/**
	 * @return bool
	 */
	protected function hasUnwind(): bool
	{
		return Arr::hasContent( $this->unwind );
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	protected function evalWithout( array $data ): ?array
	{
		foreach ( $this->without as $without ) {
			Arr::unset( $data, $without );
		}
		
		return Arr::empty( $data )
			? null
			: $data;
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	protected function evalWith( array $data ): ?array
	{
		if ( !$this->hasWith() ) {
			return $data;
		}
		
		$arr = [];
		
		foreach ( $this->with as $with ) {
			Arr::set( $arr, $with, Arr::get( $data, $with ) );
		}
		
		return Arr::empty( $arr )
			? null
			: $arr;
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	protected function evalFilter( array $data ): ?array
	{
		foreach ( $this->filter as $filter ) {
			$data = Arr::filter( $data, $filter );
		}
		
		return Arr::empty( $data )
			? null
			: $data;
	}
	
	/**
	 * @param array $data
	 *
	 * @return array|null
	 */
	protected function evalFilterRecursive( array $data ): ?array
	{
		foreach ( $this->filterRecursive as $filter ) {
			$data = Arr::filterRecursive( $data, $filter );
		}
		
		return Arr::empty( $data )
			? null
			: $data;
	}
	
	/**
	 * @param string|null $group
	 *
	 * @return bool
	 */
	protected function hasSum( string $group = null ): bool
	{
		if ( $group ) {
			return isset( $this->sum[ $group ] );
		}
		
		return Arr::hasContent( $this->sum );
	}
	
	/**
	 * @return bool
	 */
	protected function hasWith(): bool
	{
		return Arr::hasContent( $this->with );
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
				$closure = static function ( $v, $value )
				{
					return $v === $value
						? $v
						: null;
				};
				break;
			case '!=' :
				$closure = static function ( $v, $value )
				{
					return $v !== $value
						? $v
						: null;
				};
				break;
			case '>' :
				$closure = static function ( $v, $value )
				{
					return $v > $value
						? $v
						: null;
				};
				break;
			case '>=':
				$closure = static function ( $v, $value )
				{
					return $v >= $value
						? $v
						: null;
				};
				break;
			case '<':
				$closure = static function ( $v, $value )
				{
					return $v < $value
						? $v
						: null;
				};
				break;
			case '<=':
				$closure = static function ( $v, $value )
				{
					return $v <= $value
						? $v
						: null;
				};
				break;
			case '<>' :
				$closure = static function ( $v, $value, $value2 )
				{
					return $v < $value && $v > $value2
						? $v
						: null;
				};
				break;
			case '<=>' :
				$closure = static function ( $v, $value, $value2 )
				{
					return $v <= $value && $v >= $value2
						? $v
						: null;
				};
				break;
			case 'in':
				$closure = static function ( $v, $value )
				{
					return Arr::in( $v, (array) $value )
						? $v
						: null;
				};
				break;
			case '!in':
				$closure = static function ( $v, $value )
				{
					return !Arr::in( $v, (array) $value )
						? $v
						: null;
				};
				break;
			case 'type':
				$closure = static function ( $v, $value )
				{
					return Str::type( $v ) === $value
						? $v
						: null;
				};
				break;
			case '%':
				$closure = static function ( $v, $value, $value2 )
				{
					return $v % $value === $value2
						? $v
						: null;
				};
				break;
			case 'size':
				$closure = static function ( $v, $value )
				{
					return count( (array) $v ) === $value
						? $v
						: null;
				};
				break;
			case 'exist':
				$closure = static function ( $v )
				{
					return $v
						? $v
						: null;
				};
				break;
			case 'regex':
				$closure = static function ( $v, $value, $value2 )
				{
					return Str::match( '/' . $value . '/' . ( $value2 ?? 'i' ), $v )
						? $v
						: null;
				};
				break;
			case 'match':
				$closure = static function ( $v, $value )
				{
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
				$closure = static function ( $v, $value )
				{
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
	protected function hasSort(): bool
	{
		return Arr::hasContent( $this->sort );
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
				$row[ $path ] = Arr::get( $v, $path );
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
		$this->collection = [];
		
		foreach ( end( $args ) as $v ) {
			$this->collection[] = $v[ '__RAW__' ];
		}
	}
}