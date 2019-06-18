<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Json\Json;

/**
 * Mongo Schema properties.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Property
{
    /**
     * @var Json
     */
    protected $property;

    /**
     * Property constructor.
     * @param array $property
     */
    public function __construct( Array $property = [] )
    {
        $this->property = new Json();

        foreach ( $property as $key => $value ) {
            switch ( $key ) {
                case 'properties' :
                    $this->setProperties((array) $value);
                    break;
                case 'bsonType' :
                    $this->setType((array) $value);
                    break;
                case 'description':
                    $this->setDescription((string) $value);
                    break;
                case 'minimum' :
                    $this->setMin((int) $value);
                    break;
                case 'maximum' :
                    $this->setMax((int) $value);
                    break;
                case 'enum' :
                    $this->setList((array) $value);
                    break;
                case 'minItems':
                    $this->setMinItems((int) $value);
                    break;
                case 'maxItems':
                    $this->setMaxItems((int) $value);
                    break;
                case 'items':
                    $this->setItems((array) $value);
                    break;
                case 'additionalProperties' :
                    $this->setLocked((bool) $value);
                    break;
                case 'required' :
                    $this->setRequired((array) $value);
                    break;
            }
        }
    }

    /**
     * @param array $value
     * @return Property
     */
    public function setProperties( array $value ): self
    {
        $properties = $this->property->offsetGetOrSet('properties', []);

        foreach ( $value as $k => $v ) {
            $properties->offsetSet($k, new Property((array) $v));
        }

        return $this;
    }

    /**
     * @param string|array $value
     * @return Property
     */
    public function setType( $value ): self
    {
        $value = (array) $value;

        if ( count($value) == 1 ) {
            $value = reset($value);
        }

        $this->property->offsetSet('bsonType', $value);

        return $this;
    }

    /**
     * @param string $value
     * @return Property
     */
    public function setDescription( string $value ): self
    {
        $this->property->offsetSet('description', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return Property
     */
    public function setMin( int $value ): self
    {
        $this->property->offsetSet('minimum', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return Property
     */
    public function setMax( int $value ): self
    {
        $this->property->offsetSet('maximum', $value);

        return $this;
    }

    /**
     * @param mixed ...$values
     * @return Property
     */
    public function setList( ...$values ): self
    {
        $list = $this->list();

        foreach ( $values as $value ) {
            foreach ( (array) $value as $v ) {
                $list->append($v);
            }
        }

        return $this;
    }

    /**
     * @return Json
     */
    public function List(): Json
    {
        return $this->property->offsetGetOrSet('enum');
    }

    /**
     * @param int $value
     * @return Property
     */
    public function setMinItems( int $value ): self
    {
        $this->property->offsetSet('minItems', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return Property
     */
    public function setMaxItems( int $value ): self
    {
        $this->property->offsetSet('maxItems', $value);

        return $this;
    }

    /**
     * @param array $value
     * @return Property
     */
    public function setItems( array $value ): self
    {
        $this->property->offsetSet('items', new Property($value));

        return $this;
    }

    /**
     * @param bool $value
     * @return Property
     */
    public function setLocked( bool $value ): self
    {
        $this->property->offsetSet('additionalProperties', $value);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Property
     */
    public function setRequired( ...$fields ): self
    {
        $required = $this->required();

        foreach ( $fields as $field ) {
            foreach ( (array) $field as $f ) {
                $required->append($f);
            }
        }

        return $this;
    }

    /**
     * @return Json
     */
    public function required(): Json
    {
        return $this->property->offsetGetOrSet('required');
    }

    /**
     * @param mixed ...$values
     * @return Property
     */
    public function unsetList( ...$values ): self
    {
        $list = $this->list();

        foreach ( $values as $value ) {
            foreach ( (array) $value as $v ) {
                if ( ( $indexOf = $list->indexOf($v) ) !== null ) {
                    $list->offsetUnset($indexOf);
                }
            }
        }
    }

    /**
     * @return Property
     */
    public function resetList(): self
    {
        $this->property->offsetSet('enum', []);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Property
     */
    public function unsetRequired( ...$fields ): self
    {
        $required = $this->required();

        foreach ( $fields as $field ) {
            foreach ( (array) $field as $f ) {
                if ( ( $indexOf = $required->indexOf($f) ) !== null ) {
                    $required->offsetUnset($indexOf);
                }
            }
        }

        $required->resetKeys();

        return $this;
    }

    /**
     * @return Property
     */
    public function resetRequired(): self
    {
        $this->property->offsetSet('required', []);

        return $this;
    }

    /**
     * @return Property|null
     */
    public function items(): ?Property
    {
        return $this->property->offsetGet('items');
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->offsetGetOrSet('properties', []);
    }

    /**
     * @return bool|null
     */
    public function locked(): ?bool
    {
        return $this->property->offsetGet('additionalProperties');
    }

    /**
     * @return int|null
     */
    public function minItems(): ?int
    {
        return $this->property->offsetGet('minItems');
    }

    /**
     * @return int|null
     */
    public function maxItems(): ?int
    {
        return $this->property->offsetGet('maxItems');
    }

    /**
     * @return int|null
     */
    public function max(): ?int
    {
        return $this->property->offsetGet('maximum');
    }

    /**
     * @return int|null
     */
    public function min(): ?int
    {
        return $this->property->offsetGet('minimum');
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->property->offsetGet('description');
    }

    /**
     * @return string|null
     */
    public function type(): ?string
    {
        return $this->property->offsetGet('bsonType');
    }

    /**
     * @return array
     */
    public function schema(): array
    {
        return $this->property->filterRecursive(function( $k, $v )
        {
            return $v instanceof Property
                ? $v->schema()
                : $v;
        })
            ->toArray();
    }
}