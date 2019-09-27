<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Helper\Str;
use Chukdo\Json\Arr;
use Chukdo\Helper\Arr as ArrHelper;
use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Server Schema properties.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Property implements PropertyInterface
{
	/**
	 * @var Json
	 */
	protected $property;

	/**
	 * @var string|null
	 */
	protected $name = null;

	/**
	 * Property constructor.
	 *
	 * @param array       $property
	 * @param string|null $name
	 */
	public function __construct( Array $property = [], string $name = null )
	{
		$this->name     = $name;
		$this->property = new Json();

		foreach ( $property as $key => $value ) {
			switch ( $key ) {
				case 'properties' :
					$this->setProperties( (array) $value );
					break;
				case 'bsonType' :
					$this->setType( (array) $value );
					break;
				case 'description':
					$this->setDescription( (string) $value );
					break;
				case 'pattern':
					$this->setPattern( (string) $value );
					break;
				case 'minimum' :
					$this->setMin( (int) $value );
					break;
				case 'maximum' :
					$this->setMax( (int) $value );
					break;
				case 'enum' :
					$this->setList( (array) $value );
					break;
				case 'minItems':
					$this->setMinItems( (int) $value );
					break;
				case 'maxItems':
					$this->setMaxItems( (int) $value );
					break;
				case 'items':
					$this->setItems( (array) $value );
					break;
				case 'required' :
					$this->setRequired( (array) $value );
					break;
			}
		}
	}

	/**
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setProperties( array $value ): self
	{
		$properties = $this->property->offsetGetOrSet( 'properties', [] );

		foreach ( $value as $k => $v ) {
			$properties->offsetSet( $k, new Property( (array) $v, $k ) );
		}

		return $this;
	}

	/**
	 * @param string|array $value
	 *
	 * @return $this
	 */
	public function setType( $value ): self
	{
		$value = (array) $value;

		if ( count( $value ) == 1 ) {
			$value = reset( $value );
		}

		$this->property->offsetSet( 'bsonType', $value );

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setDescription( string $value ): self
	{
		$this->property->offsetSet( 'description', $value );

		return $this;
	}

	/**
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setPattern( string $value ): self
	{
		$this->property->offsetSet( 'pattern', $value );

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMin( int $value ): self
	{
		$this->property->offsetSet( 'minimum', $value );

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMax( int $value ): self
	{
		$this->property->offsetSet( 'maximum', $value );

		return $this;
	}

	/**
	 * @param mixed ...$values
	 *
	 * @return $this
	 */
	public function setList( ...$values ): self
	{
		$list = $this->list();

		foreach ( $values as $value ) {
			foreach ( (array) $value as $v ) {
				$list->appendIfNoExist( $v );
			}
		}

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function list(): JsonInterface
	{
		return $this->property->offsetGetOrSet( 'enum' );
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMinItems( int $value ): self
	{
		$this->property->offsetSet( 'minItems', $value );

		return $this;
	}

	/**
	 * @param int $value
	 *
	 * @return $this
	 */
	public function setMaxItems( int $value ): self
	{
		$this->property->offsetSet( 'maxItems', $value );

		return $this;
	}

	/**
	 * @param array $value
	 *
	 * @return $this
	 */
	public function setItems( array $value ): self
	{
		$this->property->offsetSet( 'items', new Property( $value, 'items' ) );

		return $this;
	}

	/**
	 * @param mixed ...$fields
	 *
	 * @return $this
	 */
	public function setRequired( ...$fields ): self
	{
		$required = $this->required();

		foreach ( ArrHelper::spreadArgs( $fields ) as $field ) {
			foreach ( (array) $field as $f ) {
				$required->appendIfNoExist( $f );
			}
		}

		return $this;
	}

	/**
	 * @return JsonInterface
	 */
	public function required(): JsonInterface
	{
		return $this->property->offsetGetOrSet( 'required' );
	}

	/**
	 * @return int
	 */
	public function count(): int
	{
		return $this->property->count();
	}

	/**
	 * @param bool $value
	 *
	 * @return Property
	 */
	public function setUniqueItems( bool $value ): self
	{
		$this->property->offsetSet( 'uniqueItems', $value );

		return $this;
	}

	/**
	 * @param string|null $field
	 *
	 * @return bool
	 */
	public function isRequired( string $field = null ): bool
	{
		foreach ( $this->required() as $required ) {
			if ( $required == $field ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param mixed ...$values
	 *
	 * @return $this
	 */
	public function unsetList( ...$values ): self
	{
		$list = $this->list();

		foreach ( $values as $value ) {
			foreach ( (array) $value as $v ) {
				if ( ( $indexOf = $list->indexOf( $v ) ) !== null ) {
					$list->offsetUnset( $indexOf );
				}
			}
		}
	}

	/**
	 * @return $this
	 */
	public function resetList(): self
	{
		$this->property->offsetSet( 'enum', [] );

		return $this;
	}

	/**
	 * @param mixed ...$fields
	 *
	 * @return $this
	 */
	public function unsetRequired( ...$fields ): self
	{
		$required = $this->required();

		foreach ( $fields as $field ) {
			foreach ( (array) $field as $f ) {
				if ( ( $indexOf = $required->indexOf( $f ) ) !== null ) {
					$required->offsetUnset( $indexOf );
				}
			}
		}

		$required->resetKeys();

		return $this;
	}

	/**
	 * @return $this
	 */
	public function resetRequired(): self
	{
		$this->property->offsetSet( 'required', [] );

		return $this;
	}

	/**
	 * @return $this|null
	 */
	public function items(): ?Property
	{
		return $this->property->offsetGet( 'items' );
	}

	/**
	 * @param string $name
	 *
	 * @return Property|null
	 */
	public function get( string $name ): ?Property
	{
		if ( Str::notContain( $name, '.' ) ) {
			return $this->properties()
						->offsetGet( $name );
		}

		$arr       = new Arr( Str::split( $name, '.' ) );
		$firstPath = $arr->getFirstAndRemove();
		$endPath   = $arr->join( '.' );
		$get       = $this->properties()
						  ->offsetGet( $firstPath );

		if ( $get instanceof PropertyInterface ) {
			return $get->get( $endPath );
		}

		return null;
	}

	/**
	 * @return JsonInterface
	 */
	public function properties(): JsonInterface
	{
		return $this->property->offsetGetOrSet( 'properties', [] );
	}

	/**
	 * @param string      $name
	 * @param string|null $type
	 * @param array       $options
	 *
	 * @return Property
	 */
	public function set( string $name, string $type = null, array $options = [] ): Property
	{
		$property = new Property( $options, $name );

		if ( $type ) {
			$property->setType( $type );
		}

		$this->properties()
			 ->offsetSet( $name, $property );

		return $property;
	}

	/**
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->property->filterRecursive( function( $k, $v ) {
			return $v instanceof Property
				? $v->toArray()
				: $v;
		} )
							  ->toArray();
	}

	/**
	 * @return string|null
	 */
	public function name(): ?string
	{
		return $this->name;
	}

	/**
	 * @return mixed|string|null
	 */
	public function type()
	{
		return $this->property->offsetGet( 'bsonType' );
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function unset( string $name ): self
	{
		$this->properties()
			 ->offsetUnset( $name );

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function description(): ?string
	{
		return $this->property->offsetGet( 'description' );
	}

	/**
	 * @return string|null
	 */
	public function pattern(): ?string
	{
		return $this->property->offsetGet( 'pattern' );
	}

	/**
	 * @return int|null
	 */
	public function min(): ?int
	{
		return $this->property->offsetGet( 'minimum' );
	}

	/**
	 * @return int|null
	 */
	public function max(): ?int
	{
		return $this->property->offsetGet( 'maximum' );
	}

	/**
	 * @return int|null
	 */
	public function minItems(): ?int
	{
		return $this->property->offsetGet( 'minItems' );
	}

	/**
	 * @return int|null
	 */
	public function maxItems(): ?int
	{
		return $this->property->offsetGet( 'maxItems' );
	}

	/**
	 * @return bool|null
	 */
	public function uniqueItems(): ?bool
	{
		return $this->property->offsetGet( 'uniqueItems' );
	}
}