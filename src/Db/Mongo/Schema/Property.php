<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Json\Json;
use ReflectionClass;
use Exception;

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
     * @var string|null
     */
    protected $name = null;

    /**
     * Property constructor.
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
                    $this->setProperties((array) $value);
                    break;
                case 'bsonType' :
                    $this->setType((array) $value);
                    break;
                case 'description':
                    $this->setDescription((string) $value);
                    break;
                case 'pattern':
                    $this->setPattern((string) $value);
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
     * @return $this
     */
    public function setProperties( array $value ): self
    {
        $properties = $this->property->offsetGetOrSet('properties', []);

        foreach ( $value as $k => $v ) {
            $properties->offsetSet($k, $this->newClass([
                (array) $v,
                $k,
            ]));
        }

        return $this;
    }

    /**
     * @param string|array $value
     * @return $this
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
     * @return $this
     */
    public function setDescription( string $value ): self
    {
        $this->property->offsetSet('description', $value);

        return $this;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPattern( string $value ): self
    {
        $this->property->offsetSet('pattern', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMin( int $value ): self
    {
        $this->property->offsetSet('minimum', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMax( int $value ): self
    {
        $this->property->offsetSet('maximum', $value);

        return $this;
    }

    /**
     * @param mixed ...$values
     * @return $this
     */
    public function setList( ...$values ): self
    {
        $list = $this->list();

        foreach ( $values as $value ) {
            foreach ( (array) $value as $v ) {
                $list->appendIfNoExist($v);
            }
        }

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMinItems( int $value ): self
    {
        $this->property->offsetSet('minItems', $value);

        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setMaxItems( int $value ): self
    {
        $this->property->offsetSet('maxItems', $value);

        return $this;
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setItems( array $value ): self
    {
        $this->property->offsetSet('items', $this->newClass([$value]));

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setLocked( bool $value ): self
    {
        $this->property->offsetSet('additionalProperties', $value);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return $this
     */
    public function setRequired( ...$fields ): self
    {
        $required = $this->required();

        foreach ( $fields as $field ) {
            foreach ( (array) $field as $f ) {
                $required->appendIfNoExist($f);
            }
        }

        return $this;
    }

    /**
     * @param array $params
     * @return mixed
     */
    protected function newClass( array $params = [] )
    {
        try {
            $rc = new ReflectionClass(get_called_class());
            $rc->newInstanceArgs($params);

            return call_user_func_array([
                $rc,
                'newInstance',
            ],
                $params);
        } catch ( Exception $e ) {
        }
    }

    /**
     * @return Json
     */
    public function List(): Json
    {
        return $this->property->offsetGetOrSet('enum');
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
     * @return $this
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
     * @return $this
     */
    public function resetList(): self
    {
        $this->property->offsetSet('enum', []);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return $this
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
     * @return $this
     */
    public function resetRequired(): self
    {
        $this->property->offsetSet('required', []);

        return $this;
    }

    /**
     * @return $this|null
     */
    public function items(): ?Property
    {
        return $this->property->offsetGet('items');
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setProperty( string $name ): Property
    {
        return $this->properties()
            ->offsetGetOrSet($name, $this->newClass());
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->offsetGetOrSet('properties', []);
    }

    /**
     * @param string $name
     * @return $this
     */
    public function unsetProperty( string $name ): self
    {
        $this->properties()
            ->offsetUnset($name);

        return $this;
    }

    /**
     * @return bool|null
     */
    public function locked(): ?bool
    {
        return $this->property->offsetGet('additionalProperties');
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->property->offsetGet('description');
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->property->filterRecursive(function( $k, $v )
        {
            return $v instanceof Property
                ? $v->get()
                : $v;
        })
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
     * @return string|null
     */
    public function type(): ?string
    {
        return $this->property->offsetGet('bsonType');
    }

    /**
     * @return string|null
     */
    public function pattern(): ?string
    {
        return $this->property->offsetGet('pattern');
    }

    /**
     * @return int|null
     */
    public function min(): ?int
    {
        return $this->property->offsetGet('minimum');
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
}