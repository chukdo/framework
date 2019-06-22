<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Db\Mongo\MongoException;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use DateTime;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;

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
            $properties->offsetSet($k, new Property((array) $v, $k));
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
        $this->property->offsetSet('items', new Property($value));

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
            ->offsetGetOrSet($name, new Property());
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
     * @param array|Json s$data
     * @return array
     */
    public function validate( $data ): array
    {
        $json = new Json($data);

        if ( $this->property->count() == 0 ) {
            return $json->toArray();
        }

        return $this->validateObject($json)->toArray();
    }

    /**
     * @param Json $json
     * @return Json
     */
    protected function validateProperty( Json $json ): Json
    {
        $getData = $json->get($this->name());

        switch ( $this->type() ) {
            case 'objectId' :
                $validatedData = $this->validateObjectId($getData);
                break;
            case 'string' :
                $validatedData = $this->validateString($getData);
                break;
            case 'int':
            case 'long':
                $validatedData = $this->validateInt($getData);
                break;
            case 'decimal':
            case 'double' :
                $validatedData = $this->validatefloat($getData);
                break;
            case 'boolean' :
                $validatedData = $this->validateBool($getData);
                break;
            case 'date' :
                $validatedData = $this->validateDate($getData);
                break;
            case 'timestamp' :
                $validatedData = $this->validateTimestamp($getData);
                break;
            case 'enum' :
                $validatedData = $this->validateList($getData);
                break;
            case 'array':
                $validatedData = $this->validateArray($getData);
                break;
            case 'object':
                $validatedData = $this->validateObject($getData);
                break;
            default :
                $enum = $this->property->offsetGet('enum');

                if ( $enum ) {
                    $validatedData = $this->validateList($getData);
                }
                else {
                    throw new MongoException(sprintf("The field [%s] must be a valid type not [%s]", $this->name(), $this->type()));
                }
        }

        $json->set($this->name(), $validatedData);

        return $json;
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
     * @param $data
     * @return ObjectId
     */
    protected function validateObjectId( $data )
    {
        if ( $data instanceof ObjectId ) {
            return $data;
        }
        elseif ( Is::string($data) ) {
            return new ObjectId($data);
        }

        throw new MongoException(sprintf("The field [%s] must be a objectId", $this->name()));
    }

    /**
     * @param $data
     * @return string
     */
    protected function validateString( $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a string", $this->name()));
        }

        $data    = (string) $data;
        $pattern = $this->pattern();

        if ( $pattern ) {
            if ( !Str::match('/' . $pattern . '/i', $data) ) {
                throw new MongoException(sprintf("The field [%s] must be a string with pattern [%s]", $this->name(), $pattern));
            }
        }

        return $data;
    }

    /**
     * @param $data
     * @return int
     */
    protected function validateInt( $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a int", $this->name()));
        }

        $data = (int) $data;

        $this->checkMin($data);
        $this->checkMax($data);

        return $data;
    }

    /**
     * @param $data
     * @return float
     */
    protected function validateFloat( $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a float", $this->name()));
        }

        $data = (float) $data;

        $this->checkMin($data);
        $this->checkMax($data);

        return $data;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function validateBool( $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a bool", $this->name()));
        }

        $data = (bool) $data;

        return $data;
    }

    /**
     * @param $data
     * @return UTCDateTime
     */
    protected function validateDate( $data )
    {
        if ( $data instanceof UTCDateTime ) {
            return $data;
        }
        elseif ( $data instanceof DateTime ) {
            return new UTCDateTime($data->getTimestamp());
        }
        elseif ( Is::scalar($data) ) {
            return new UTCDateTime((int) $data);
        }

        throw new MongoException(sprintf("The field [%s] must be a date", $this->name()));
    }

    /**
     * @param $data
     * @return Timestamp
     */
    protected function validateTimestamp( $data )
    {
        if ( $data instanceof Timestamp ) {
            return $data;
        }
        elseif ( $data instanceof DateTime ) {
            return new Timestamp($data->getTimestamp(), 1);
        }
        elseif ( Is::scalar($data) ) {
            return new Timestamp((int) $data, 1);
        }

        throw new MongoException(sprintf("The field [%s] must be a timestamp", $this->name()));
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function validateList( $data )
    {
        $list = $this->list();

        if ( !Is::scalar($data) || !$list->in($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a element of list [%s]", $this->name(), implode(',', $list->toArray())));
        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function validateArray( $data )
    {
        if ( !( $data instanceof Json ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $this->name()));
        }

        $this->checkMinItems($data);
        $this->checkMaxItems($data);

        foreach ( $this->properties() as $key => $property ) {
            $property->validateProperty($data);
        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function validateObject( $data )
    {
        if ( !( $data instanceof Json ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $this->name()));
        }

        foreach ( $this->required() as $required ) {
            if ( $data->get($required) === null ) {
                throw new MongoException(sprintf("The field [%s] is required", $required));
            }
        }

        foreach ( $this->properties() as $key => $property ) {
            $property->validateProperty($data);
        }

        return $data;
    }

    /**
     * @return string|null
     */
    public function pattern(): ?string
    {
        return $this->property->offsetGet('pattern');
    }

    /**
     * @param $data
     */
    protected function checkMin( $data )
    {
        if ( $min = $this->min() ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $this->name(), $min));
            }
        }
    }

    /**
     * @param $data
     */
    protected function checkMax( $data )
    {
        if ( $max = $this->max() ) {
            if ( $data > $max ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $this->name(), $max));
            }
        }
    }

    /**
     * @param Json $data
     */
    protected function checkMinItems( Json $data )
    {
        if ( $min = $this->minItems() ) {
            if ( $data->count() < $min ) {
                throw new MongoException(sprintf("The field [%s] must have more than [%s] items", $this->name(), $min));
            }
        }
    }

    /**
     * @param Json $data
     */
    protected function checkMaxItems( Json $data )
    {
        if ( $max = $this->maxItems() ) {
            if ( $data->count() > $max ) {
                throw new MongoException(sprintf("The field [%s] must have less than [%s] items", $this->name(), $max));
            }
        }
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