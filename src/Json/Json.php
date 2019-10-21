<?php

namespace Chukdo\Json;

use Chukdo\Contracts\Json\Json as JsonInterface;
use ArrayObject;
use Chukdo\Helper\Cli;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Helper\To;
use Chukdo\Helper\Arr as ArrHelper;
use Chukdo\Xml\Xml;
use Closure;
use League\CLImate\CLImate;

/**
 * Manipulation des données.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Json extends ArrayObject implements JsonInterface
{
	/**
	 * @var bool $strict
	 */
	protected $strict = true;

	/**
	 * Json constructor.
	 *
	 * @param null $data
	 * @param bool $strict
	 */
	public function __construct( $data = null, bool $strict = true )
	{
		$this->strict = $strict;

		parent::__construct( [] );

		if ( Is::iterable( $data ) ) {
			foreach ( $data as $k => $v ) {
				$this->offsetSet( $k, $v );
			}
		} else if ( Is::jsonString( $data ) ) {
			foreach ( json_decode( $data, true, 512, JSON_THROW_ON_ERROR ) as $k => $v ) {
				$this->offsetSet( $k, $v );
			}
		} else if ( !Is::null( $data ) ) {
			throw new JsonException( 'Data must be null or Iterable or JsonString' );
		}
	}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return JsonInterface
	 */
	public function offsetSet( $key, $value ): JsonInterface
	{
		if ( ( $this->strict === true && Is::iterable( $value ) ) || ( Is::arr( $value ) && !Is::jsonInterface( $value ) ) ) {
			parent::offsetSet( $key, new Json( $value, $this->strict ) );
		} else {
			parent::offsetSet( $key, $value );
		}

		return $this;
	}

	/**
	 * @param $key
	 *
	 * @return JsonInterface
	 */
	public function coll( $key ): JsonInterface
	{
		$coll = new Json();

		foreach ( $this as $offsetKey => $offsetValue ) {
			if ( $key === $offsetKey ) {
				$coll->append( $offsetValue );
			}
		}

		return $coll;
	}

	/**
	 * @param mixed $value
	 *
	 * @return JsonInterface
	 */
	public function append( $value ): JsonInterface
	{
		if ( ( $this->strict === true && Is::iterable( $value ) ) || ( Is::arr( $value ) && !Is::jsonInterface( $value ) ) ) {
			parent::append( new Json( $value, $this->strict ) );
		} else {
			parent::append( $value );
		}

		return $this;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function __get( string $key )
	{
		return $this->offsetExists( $key )
			? $this->offsetGet( $key )
			: null;
	}

	/**
	 * @param string $key
	 * @param        $value
	 */
	public function __set( string $key, $value ): void
	{
		$this->offsetSet( $key, $value );
	}

	/**
	 * @param mixed $key
	 *
	 * @return bool
	 */
	public function offsetExists( $key ): bool
	{
		return parent::offsetExists( $key );
	}

	/**
	 * @param mixed $key
	 * @param null  $default
	 *
	 * @return mixed|null
	 */
	public function offsetGet( $key, $default = null )
	{
		if ( $this->offsetExists( $key ) ) {
			return parent::offsetGet( $key );
		}

		return $default;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset( string $key ): bool
	{
		return $this->offsetExists( $key );
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->toJson( true );
	}

	/**
	 * @return array
	 */
	public function __debugInfo(): array
	{
		return $this->getArrayCopy();
	}

	/**
	 * @param bool $prettify
	 *
	 * @return string
	 */
	public function toJson( bool $prettify = false ): string
	{
		return json_encode( $this->toArray(),
			$prettify
				? JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
				: JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR, 512 );
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->getArrayCopy();
	}

	/**
	 * @return array
	 */
	public function getArrayCopy(): array
	{
		$array = parent::getArrayCopy();

		/** Iteration de l'objet pour convertir
		 * les sous elements data_array en array */
		foreach ( $array as $k => $v ) {
			if ( $v instanceof arrayObject ) {
				$array[ $k ] = $v->getArrayCopy();
			}
		}

		return $array;
	}

	/**
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __unset( string $key ): bool
	{
		return (bool) $this->offsetUnset( $key );
	}

	/**
	 * @param mixed $key
	 *
	 * @return mixed|null
	 */
	public function offsetUnset( $key )
	{
		if ( $this->offsetExists( $key ) ) {
			$offset = parent::offsetGet( $key );
			parent::offsetUnset( $key );

			return $offset;
		}

		return null;
	}

	/**
	 * @param iterable $data
	 *
	 * @return JsonInterface
	 */
	public function addToSet( iterable $data ): JsonInterface
	{
		foreach ( $data as $k => $v ) {
			if ( !$this->in( $v ) ) {
				$this->append( $v );
			}
		}

		return $this;
	}

	/**
	 * @param $value
	 *
	 * @return bool
	 */
	public function in( $value ): bool
	{
		foreach ( $this as $v ) {
			if ( $v === $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return $this
	 */
	public function all(): self
	{
		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return JsonInterface
	 */
	public function appendIfNoExist( $value ): JsonInterface
	{
		foreach ( $this as $k => $v ) {
			if ( $v === $value ) {
				return $this;
			}
		}

		return $this->append( $value );
	}

	/**
	 * @return JsonInterface
	 */
	public function clean(): JsonInterface
	{
		$json = new Json();

		foreach ( $this as $k => $v ) {
			if ( $v !== null && $v !== '' ) {
				$json->offsetSet( $k, $v );
			}
		}

		return $json;
	}

	/**
	 * @param JsonInterface $json
	 * @param bool          $flat
	 *
	 * @return JsonInterface
	 */
	public function diff( JsonInterface $json, bool $flat = false ): JsonInterface
	{
		$src  = $this->to2d();
		$new  = $json->to2d();
		$diff = new Json();
		$set  = static function( $path, $data ) use ( $diff, $flat ) {
			$flat
				? $diff->append( ArrHelper::merge( [ 'path' => $path ], $data ) )
				: $diff->set( $path, $data );
		};

		foreach ( $src as $path => $srcValue ) {
			if ( $newValue = $new->offsetUnset( $path ) ) {

				/** Common */
				if ( $srcValue === $newValue ) {
					$set( $path, [
						'op'    => 'common',
						'value' => $srcValue,
					] );
				} /** Replace */
				else {
					$set( $path, [
						'op'    => 'replace',
						'old'   => $srcValue,
						'value' => $newValue,
					] );
				}
			} /** Delete */
			else {
				$set( $path, [
					'op' => 'remove',
				] );
			}
		}

		/** Add */
		foreach ( $new as $path => $newValue ) {
			$set( $path, [
				'op'    => 'add',
				'value' => $newValue,
			] );
		}

		return $diff;
	}

	/**
	 * @param string|null $prefix
	 *
	 * @return JsonInterface
	 */
	public function to2d( string $prefix = null ): JsonInterface
	{
		$mixed = new Json();

		foreach ( $this as $k => $v ) {
			$k = trim( $prefix . '.' . $k, '.' );

			if ( $v instanceof JsonInterface ) {
				$mixed->merge( $v->to2d( $k ) );
			} else {
				$mixed->offsetSet( $k, $v );
			}
		}

		return $mixed;
	}

	/**
	 * @param iterable|null $merge
	 * @param bool|null     $overwrite
	 *
	 * @return JsonInterface
	 */
	public function merge( iterable $merge = null, bool $overwrite = null ): JsonInterface
	{
		if ( $merge ) {
			foreach ( $merge as $k => $v ) {
				if ( $overwrite || !$this->offsetExists( $k ) ) {
					$this->offsetSet( $k, $v );
				}
			}
		}

		return $this;
	}

	/**
	 * @param iterable|null $push
	 * @param bool|null     $overwrite
	 *
	 * @return JsonInterface
	 */
	public function push( iterable $push = null, bool $overwrite = null ): JsonInterface
	{
		if ( $push ) {
			foreach ( $push as $k => $v ) {
				if ( is_int( $k ) ) {
					$this->append( $v );
				} else if ( $overwrite || !$this->offsetExists( $k ) ) {
					$this->offsetSet( $k, $v );
				}
			}
		}

		return $this;
	}

	/**
	 * @param string $path
	 * @param        $value
	 *
	 * @return JsonInterface
	 */
	public function set( string $path, $value ): JsonInterface
	{
		if ( Str::notContain( $path, '.' ) ) {
			return $this->offsetSet( $path, $value );
		}

		$arr       = new Arr( Str::split( $path, '.' ) );
		$firstPath = $arr->getFirstAndRemove();
		$endPath   = $arr->join( '.' );

		$this->offsetGetOrSet( $firstPath )
			 ->set( $endPath, $value );

		return $this;
	}

	/**
	 * @param       $key
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function offsetGetOrSet( $key, $value = null )
	{
		if ( $this->offsetExists( $key ) ) {
			return parent::offsetGet( $key );
		}

		return $this->offsetSet( $key,
			$value
				?: [] )
					->offsetGet( $key );
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function exists( string $path ): bool
	{
		$value = $this->get( $path );

		return $value !== null;
	}

	/**
	 * @param string|null $path
	 * @param null        $default
	 *
	 * @return mixed|null
	 */
	public function get( ?string $path, $default = null )
	{
		if ( $path === null ) {
			return $default;
		}

		if ( Str::notContain( $path, '.' ) ) {
			return $this->offsetGet( $path, $default );
		}

		$arr       = new Arr( Str::split( $path, '.' ) );
		$firstPath = $arr->getFirstAndRemove();
		$endPath   = $arr->join( '.' );
		$get       = $this->offsetGet( $firstPath );

		if ( $get instanceof JsonInterface ) {
			return $get->get( $endPath, $default );
		}

		return $default;
	}

	/**
	 * @param string|null $path
	 *
	 * @return JsonInterface
	 */
	public function getJson( ?string $path ): JsonInterface
	{
		return $this->get( $path, new Json() );
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function filled( string $path ): bool
	{
		$value = $this->get( $path );

		return $value !== null && $value !== '';
	}

	/**
	 * @param Closure $closure
	 *
	 * @return JsonInterface
	 */
	public function filter( Closure $closure ): JsonInterface
	{
		$json = new Json();

		foreach ( $this as $k => $v ) {
			$r = $closure( $k, $v );

			if ( $r !== null ) {
				$json->offsetSet( $k, $r );
			}
		}

		return $json;
	}

	/**
	 * @param Closure $closure
	 *
	 * @return JsonInterface
	 */
	public function filterRecursive( Closure $closure ): JsonInterface
	{
		$json = new Json();

		foreach ( $this as $k => $v ) {
			if ( $v instanceof JsonInterface ) {
				$r = $v->filterRecursive( $closure );

				if ( $r->count() > 0 ) {
					$json->offsetSet( $k, $r );
				}
			} else {
				$r = $closure( $k, $v );

				if ( $r !== null ) {
					$json->offsetSet( $k, $r );
				}
			}
		}

		return $json;
	}

	/**
	 * @return mixed
	 */
	public function getFirst()
	{
		return $this->getIndex( 0 );
	}

	/**
	 * @param int   $key
	 * @param mixed $default
	 *
	 * @return mixed|null
	 */
	public function getIndex( int $key = 0, $default = null )
	{
		$index = 0;

		if ( $this->count() > 0 ) {
			foreach ( $this as $value ) {
				if ( $key === $index ) {
					return $value;
				}

				++$index;
			}
		}

		return $default;
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return parent::count();
	}

	/**
	 * @param int $key
	 *
	 * @return JsonInterface
	 */
	public function getIndexJson( int $key = 0 ): JsonInterface
	{
		return $this->getIndex( $key, new Json() );
	}

	/**
	 * @return mixed|null
	 */
	public function getKeyFirst()
	{
		foreach ( $this as $key => $unused ) {
			return $key;
		}

		return null;
	}

	/**
	 * @param int  $index
	 * @param null $default
	 *
	 * @return int|mixed|string|null
	 */
	public function getKeyIndex( int $index = 0, $default = null )
	{
		if ( $this->count() > 0 ) {
			foreach ( $this as $key => $value ) {
				if ( $key === $index ) {
					return $key;
				}
			}
		}

		return $default;
	}

	/**
	 * @return mixed|null
	 */
	public function getKeyLast()
	{
		$last = null;

		foreach ( $this as $key => $unused ) {
			$last = $key;
		}

		return $last;
	}

	/**
	 * @return mixed
	 */
	public function getLast()
	{
		return $this->getIndex( $this->count() - 1 );
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	public function indexOf( $value )
	{
		foreach ( $this as $k => $v ) {
			if ( $v === $value ) {
				return $k;
			}
		}

		return null;
	}

	/**
	 * @param JsonInterface $json
	 *
	 * @return JsonInterface
	 */
	public function intersect( JsonInterface $json ): JsonInterface
	{
		$intersect = new Json();

		foreach ( $this as $key => $value ) {
			if ( $json->offsetExists( $key ) ) {
				$intersect->offsetSet( $key, $value );
			}
		}

		return $intersect;
	}

	/**
	 * @param mixed ...$param
	 *
	 * @return mixed
	 */
	public function is( ...$param )
	{
		$param      = ArrHelper::spreadArgs( $param );
		$function   = array_shift( $param );
		$param[ 0 ] = $this->get( $param[ 0 ] );

		return call_user_func_array( [
			Is::class,
			$function,
		],
			$param );
	}

	/**
	 * @return bool
	 */
	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	/**
	 * @param mixed ...$names
	 *
	 * @return JsonInterface
	 */
	public function map( ... $names ): JsonInterface
	{
		$json  = new Json();
		$names = ArrHelper::spreadArgs( $names );

		foreach ( $this as $k => $v ) {
			if ( ArrHelper::in( $k, $names ) ) {
				$json->offsetSet( $k, $v );
			}
		}

		return $json;
	}

	/**
	 * @param iterable|null $merge
	 * @param bool|null     $overwrite
	 *
	 * @return JsonInterface
	 */
	public function mergeRecursive( iterable $merge = null, bool $overwrite = null ): JsonInterface
	{
		if ( $merge ) {
			foreach ( $merge as $k => $v ) {

				/** Les deux sont iterables on boucle en recursif */
				if ( is_iterable( $v )
					&& $this->offsetGet( $k ) instanceof JsonInterface ) {
					$this->offsetGet( $k )
						 ->mergeRecursive( $v,
							 $overwrite );
					continue;
				}

				if ( $overwrite || !$this->offsetExists( $k ) ) {
					$this->offsetSet( $k, $v );
				}
			}
		}

		return $this;
	}

	/**
	 * @param array $keys
	 * @param null  $default
	 *
	 * @return mixed|null
	 */
	public function offsetGetFirstInList( array $keys, $default = null )
	{
		foreach ( $keys as $key ) {
			if ( $get = $this->offsetGet( $key ) ) {
				return $get;
			}
		}

		return $default;
	}

	/**
	 * @param string $path
	 * @param string $sort
	 *
	 * @return JsonInterface
	 */
	public function sort( string $path, string $sort = 'ASC' ): JsonInterface
	{
		$toSort = [];

		foreach ( $this as $k => $v ) {
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

		$json = new Json();

		foreach ( $toSort as $sorted ) {
			$json->offsetSet( $sorted[ 'k' ], $sorted[ 'v' ] );
		}

		return $json;
	}

	/**
	 * @param mixed ...$param
	 *
	 * @return mixed
	 */
	public function to( ...$param )
	{
		$param      = ArrHelper::spreadArgs( $param );
		$function   = array_shift( $param );
		$param[ 0 ] = $this->get( $param[ 0 ] );

		return call_user_func_array( [
			To::class,
			$function,
		], $param );
	}

	/**
	 * @param string|null $title
	 * @param string|null $color
	 *
	 * @return string
	 */
	public function toConsole( string $title = null, string $color = null ): string
	{
		if ( !Cli::runningInConsole() ) {
			throw new JsonException( 'You can call json::toConsole only in CLI mode.' );
		}

		$climate = new CLImate();
		$climate->output->defaultTo( 'buffer' );

		if ( $title ) {
			$climate->border();
			$climate->style->addCommand( 'colored', $color
				?? 'green' );
			$climate->colored( ucfirst( $title
				?? $this->name ) );
			$climate->border();
		}

		$climate->json( $this->toArray() );

		return $climate->output->get( 'buffer' )
							   ->get();
	}

	/**
	 * @param string|null $title
	 * @param string|null $color
	 *
	 * @return string
	 */
	public function toHtml( string $title = null, string $color = null ): string
	{
		return To::html( $this, $title, $color, true );
	}

	/**
	 * @return Xml
	 */
	public function toXml(): Xml
	{
		$xml = new Xml();
		$xml->import( $this->toArray() );

		return $xml;
	}

	/**
	 * @param mixed ...$offsets
	 *
	 * @return JsonInterface
	 */
	public function with( ...$offsets ): JsonInterface
	{
		$offsets = ArrHelper::spreadArgs( $offsets );
		$only    = new Json();

		foreach ( $offsets as $offset ) {
			$only->set( $offset, $this->get( $offset ) );
		}
		return $only;
	}


	/**
	 * @param mixed ...$offsets
	 *
	 * @return JsonInterface
	 */
	public function without( ... $offsets ): JsonInterface
	{
		$offsets = ArrHelper::spreadArgs( $offsets );
		$except  = new Json( $this->toArray() );

		foreach ( $offsets as $offsetList ) {
			foreach ( (array) $offsetList as $offset ) {
				$except->unset( $offset );
			}
		}

		return $except;
	}


	/**
	 * @param string $path
	 *
	 * @return mixed|null
	 */
	public function unset( string $path )
	{
		if ( Str::notContain( $path, '.' ) ) {
			return $this->offsetUnset( $path );
		}

		$arr       = new Arr( Str::split( $path, '.' ) );
		$firstPath = $arr->getFirstAndRemove();
		$endPath   = $arr->join( '.' );
		$get       = $this->offsetGet( $firstPath );

		if ( $get instanceof JsonInterface ) {
			return $get->unset( $endPath );
		}

		return null;
	}

	/**
	 * @param array $data
	 *
	 * @return JsonInterface
	 */
	public function reset( $data = [] ): JsonInterface
	{
		parent::__construct( $data );

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function resetKeys(): JsonInterface
	{
		$arr = array_values( $this->toArray() );

		return $this->reset( $arr );
	}

	/**
	 * @param string $path
	 * @param bool   $scalarResultOnly
	 *
	 * @return JsonInterface
	 */
	public function wildcard( string $path, bool $scalarResultOnly = false ): JsonInterface
	{
		$path      = rtrim( $path, '.*' );
		$arr       = new Arr( Str::split( $path, '.' ) );
		$firstPath = $arr->getFirstAndRemove();
		$emptyPath = $arr->empty();
		$endPath   = $arr->join( '.' );
		$json      = new Json( [] );
		$get       = $this->offsetGet( $firstPath );

		if ( $firstPath === '*' ) {
			foreach ( $this as $key => $value ) {
				if ( ( $value instanceof JsonInterface ) && ( $get = $value->wildcard( $endPath, $scalarResultOnly ) )->count() ) {
					$json->push( $get );
				}
			}
		} else if ( $get instanceof JsonInterface && !$emptyPath ) {
			$json->merge( $get->wildcard( $endPath, $scalarResultOnly ) );

		} else if ( $get && $emptyPath && ( ( is_scalar( $get ) && $scalarResultOnly ) || !$scalarResultOnly ) ) {
			$json->append( $get );
		}

		return $json;
	}


}
