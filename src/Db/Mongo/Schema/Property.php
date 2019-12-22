<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Iterate;
use Chukdo\Helper\Arr;
use Chukdo\Json\Json;

/**
 * Server Schema properties.
 *
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
    protected Json $property;

    /**
     * @var string|null
     */
    protected ?string $name;

    /**
     * Property constructor.
     *
     * @param array       $property
     * @param string|null $name
     */
    public function __construct( array $property = [], string $name = null )
    {
        $this->name     = $name;
        $this->property = new Json();
        foreach ( $property as $key => $value ) {
            switch ( $key ) {
                case 'properties' :
                    $this->setProperties( (array)$value );
                    break;
                case 'bsonType' :
                    $this->setType( (array)$value );
                    break;
                case 'description':
                    $this->setDescription( (string)$value );
                    break;
                case 'pattern':
                    $this->setPattern( (string)$value );
                    break;
                case 'minimum' :
                    $this->setMin( (int)$value );
                    break;
                case 'maximum' :
                    $this->setMax( (int)$value );
                    break;
                case 'enum' :
                    $this->setList( (array)$value );
                    break;
                case 'minItems':
                    $this->setMinItems( (int)$value );
                    break;
                case 'maxItems':
                    $this->setMaxItems( (int)$value );
                    break;
                case 'items':
                    $this->setItems( (array)$value );
                    break;
                case 'required' :
                    $this->setRequired( (array)$value );
                    break;
            }
        }
    }

    /**
     * @param array $value
     *
     * @return Property
     */
    public function setProperties( array $value ): Property
    {
        $properties = $this->property->offsetGetOrSet( 'properties' );
        foreach ( $value as $k => $v ) {
            $properties->offsetSet( $k, new Property( (array)$v, $k ) );
        }

        return $this;
    }

    /**
     * @param $value
     *
     * @return Property
     */
    public function setType( $value ): Property
    {
        /** Intercompatiblité propriété Mongo|Elastic */
        if ( $value === 'keyword' || $value === 'text' ) {
            $value = 'string';
        }
        $this->property->offsetSet( 'bsonType', $value );

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Property
     */
    public function setDescription( string $value ): Property
    {
        $this->property->offsetSet( 'description', $value );

        return $this;
    }

    /**
     * @param string $value
     *
     * @return Property
     */
    public function setPattern( string $value ): Property
    {
        $this->property->offsetSet( 'pattern', $value );

        return $this;
    }

    /**
     * @param int $value
     *
     * @return Property
     */
    public function setMin( int $value ): Property
    {
        $this->property->offsetSet( 'minimum', $value );

        return $this;
    }

    /**
     * @param int $value
     *
     * @return Property
     */
    public function setMax( int $value ): Property
    {
        $this->property->offsetSet( 'maximum', $value );

        return $this;
    }

    /**
     * @param mixed ...$values
     *
     * @return Property
     */
    public function setList( ...$values ): Property
    {
        $list = $this->list();
        foreach ( $values as $value ) {
            foreach ( (array)$value as $v ) {
                $list->appendIfNoExist( $v );
            }
        }

        return $this;
    }

    /**
     * @return Json
     */
    public function list(): Json
    {
        return $this->property->offsetGetOrSet( 'enum' );
    }

    /**
     * @param int $value
     *
     * @return Property
     */
    public function setMinItems( int $value ): Property
    {
        $this->property->offsetSet( 'minItems', $value );

        return $this;
    }

    /**
     * @param int $value
     *
     * @return Property
     */
    public function setMaxItems( int $value ): Property
    {
        $this->property->offsetSet( 'maxItems', $value );

        return $this;
    }

    /**
     * @param array $value
     *
     * @return Property
     */
    public function setItems( array $value ): Property
    {
        $this->property->offsetSet( 'items', new Property( $value, 'items' ) );

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return Property
     */
    public function setRequired( ...$fields ): Property
    {
        $required = $this->required();
        foreach ( Arr::spreadArgs( $fields ) as $field ) {
            foreach ( (array)$field as $f ) {
                $required->appendIfNoExist( $f );
            }
        }

        return $this;
    }

    /**
     * @return Json
     */
    public function required(): Json
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
    public function setUniqueItems( bool $value ): Property
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
            if ( $required === $field ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param mixed ...$values
     *
     * @return Property
     */
    public function unsetList( ...$values ): Property
    {
        $list = $this->list();
        foreach ( $values as $value ) {
            foreach ( (array)$value as $v ) {
                if ( ( $indexOf = $list->indexOf( $v ) ) !== null ) {
                    $list->offsetUnset( $indexOf );
                }
            }
        }

        return $this;
    }

    /**
     * @return Property
     */
    public function resetList(): Property
    {
        $this->property->offsetSet( 'enum', [] );

        return $this;
    }

    /**
     * @param mixed ...$fields
     *
     * @return Property
     */
    public function unsetRequired( ...$fields ): Property
    {
        $required = $this->required();
        foreach ( $fields as $field ) {
            foreach ( (array)$field as $f ) {
                if ( ( $indexOf = $required->indexOf( $f ) ) !== null ) {
                    $required->offsetUnset( $indexOf );
                }
            }
        }
        $required->resetKeys();

        return $this;
    }

    /**
     * @return Property
     */
    public function resetRequired(): Property
    {
        $this->property->offsetSet( 'required', [] );

        return $this;
    }

    /**
     * @return Property|null
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

        $arr       = new Iterate( Str::split( $name, '.' ) );
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join( '.' );
        $get       = $this->properties()
                          ->offsetGet( $firstPath );
        if ( $get instanceof self ) {
            return $get->get( $endPath );
        }

        return null;
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->offsetGetOrSet( 'properties' );
    }

    /**
     * @param array $properties
     *
     * @return Property
     */
    public function setAll( array $properties ): Property
    {
        foreach ( $properties as $name => $type ) {
            $this->set( $name, $type );
        }

        return $this;
    }

    /**
     * @param string $name
     * @param null   $type
     * @param array  $options
     *
     * @return Property
     */
    public function set( string $name, $type = null, array $options = [] ): Property
    {
        $property = new Property( $options, $name );
        if ( Is::string( $type ) ) {
            $property->setType( $type );
        } else {
            if ( Is::arr( $type ) ) {
                foreach ( $type as $k => $v ) {
                    $property->set( $k, $v );
                }
            }
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
        return $this->property->filterRecursive( fn($k, $v) => $v instanceof Property
            ? $v->toArray()
            : $v )->toArray();
    }

    /**
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * @return array|string|null
     */
    public function type()
    {
        return $this->property->offsetGet( 'bsonType' );
    }

    /**
     * @param string $name
     *
     * @return Property
     */
    public function unset( string $name ): Property
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