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
 * Server Schema properties validator.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * Validator constructor.
     * @param Property $property
     */
    public function __construct( Property $property )
    {
        $this->property = $property;
    }

    /**
     * @param array|Json $data
     * @return array
     */
    public function validateDataToInsert( $data ): array
    {
        $json = new Json($data);

        if ( $this->property->count() == 0 ) {
            return $json->toArray();
        }

        return $this->validateObject($json, true)
            ->toArray();
    }

    /**
     * @param      $json
     * @param bool $insert
     * @return mixed
     */
    protected function validateObject( $json, bool $insert = true )
    {
        if ( !( $json instanceof Json ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $this->property->name()));
        }

        /** Insert */
        if ( $insert ) {
            foreach ( $this->property->properties() as $key => $property ) {
                if ( $get = $json->offsetGet($key) ) {
                    $validator = new Validator($property);
                    $json->offsetSet($key, $validator->validateType($get, true));
                }
                elseif ( $this->property->isRequired($key) ) {
                    throw new MongoException(sprintf("The field [%s] is required", $key));
                }
            }
        }

        /** Update */
        else {
            foreach ( $json as $key => $value ) {
                if ( $property = $this->property->get($key) ) {
                    $validator = new Validator($property);
                    $json->offsetSet($key, $validator->validateType($value, false));
                }
            }
        }

        return $json;
    }

    /**
     * @param      $data
     * @param bool $insert
     * @return mixed
     */
    public function validateType( $data, bool $insert = true )
    {
        $type = $this->property->type();

        if ( Is::arr($type) ) {
            $count = count($type);

            foreach ( $type as $i => $t ) {
                try {
                    return $this->validateProperty($t, $data, $insert);
                } catch ( MongoException $e ) {
                    if ( $i == $count ) {
                        throw $e;
                    }
                }
            }
        }

        return $this->validateProperty($type, $data, $insert);
    }

    /**
     * @param string|null $type
     * @param             $data
     * @param bool        $insert
     * @return bool|float|int|ObjectId|Timestamp|UTCDateTime|string
     */
    protected function validateProperty( ?string $type, $data, bool $insert = true )
    {
        switch ( $type ) {
            case 'objectId' :
                $validatedData = $this->validateObjectId($data);
                break;
            case 'string' :
                $validatedData = $this->validateString($data);
                break;
            case 'int':
            case 'long':
                $validatedData = $this->validateInt($data);
                break;
            case 'decimal':
            case 'double' :
                $validatedData = $this->validatefloat($data);
                break;
            case 'boolean' :
                $validatedData = $this->validateBool($data);
                break;
            case 'date' :
                $validatedData = $this->validateDate($data);
                break;
            case 'timestamp' :
                $validatedData = $this->validateTimestamp($data);
                break;
            case 'enum' :
                $validatedData = $this->validateList($data);
                break;
            case 'array':
                $validatedData = $this->validateArray($data, $insert);
                break;
            case 'object':
                $validatedData = $this->validateObject($data, $insert);
                break;
            default :
                $enum = $this->property->list();

                if ( $enum ) {
                    $validatedData = $this->validateList($data);
                }
                else {
                    throw new MongoException(sprintf("The field [%s] must be a valid type not [%s]", $this->property->name(), $this->property->type()));
                }
        }

        return $validatedData;
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

        throw new MongoException(sprintf("The field [%s] must be a objectId", $this->property->name()));
    }

    /**
     * @param $data
     * @return string
     */
    protected function validateString( $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a string", $this->property->name()));
        }

        $data    = (string) $data;
        $pattern = $this->property->pattern();

        if ( $pattern ) {
            if ( !Str::match('/' . $pattern . '/i', $data) ) {
                throw new MongoException(sprintf("The field [%s] must be a string with pattern [%s]", $this->property->name(), $pattern));
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
            throw new MongoException(sprintf("The field [%s] must be a int", $this->property->name()));
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
            throw new MongoException(sprintf("The field [%s] must be a float", $this->property->name()));
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
            throw new MongoException(sprintf("The field [%s] must be a bool", $this->property->name()));
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

        throw new MongoException(sprintf("The field [%s] must be a date", $this->property->name()));
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

        throw new MongoException(sprintf("The field [%s] must be a timestamp", $this->property->name()));
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function validateList( $data )
    {
        $list = $this->property->list();

        if ( !Is::scalar($data) || !$list->in($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a element of list [%s]", $this->property->name(), implode(',', $list->toArray())));
        }

        return $data;
    }

    /**
     * @param      $data
     * @param bool $insert
     * @return mixed
     */
    protected function validateArray( $data, bool $insert = true )
    {
        if ( $insert ) {
            if ( !( $data instanceof Json ) ) {
                throw new MongoException(sprintf("The field [%s] must be a array", $this->property->name()));
            }

            $this->checkMinItems($data);
            $this->checkMaxItems($data);

            if ( $items = $this->property->items() ) {
                $validator = new Validator($items);

                foreach ( $data as $key => $value ) {
                    $data->offsetSet($key, $validator->validateType($value, true));
                }
            }
        }
        else {
            if ( $items = $this->property->items() ) {
                $validator = new Validator($items);
                $data      = $validator->validateType($data, false);
            }
        }

        return $data;
    }

    /**
     * @param $data
     */
    protected function checkMin( $data )
    {
        if ( $min = $this->property->min() ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $this->property->name(), $min));
            }
        }
    }

    /**
     * @param $data
     */
    protected function checkMax( $data )
    {
        if ( $max = $this->property->max() ) {
            if ( $data > $max ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $this->property->name(), $max));
            }
        }
    }

    /**
     * @param Json $data
     */
    protected function checkMinItems( Json $data )
    {
        if ( $min = $this->property->minItems() ) {
            if ( $data->count() < $min ) {
                throw new MongoException(sprintf("The field [%s] must have more than [%s] items", $this->property->name(), $min));
            }
        }
    }

    /**
     * @param Json $data
     */
    protected function checkMaxItems( Json $data )
    {
        if ( $max = $this->property->maxItems() ) {
            if ( $data->count() > $max ) {
                throw new MongoException(sprintf("The field [%s] must have less than [%s] items", $this->property->name(), $max));
            }
        }
    }

    /**
     * @param array|Json $data
     * @return array
     */
    public function validateDataToUpdate( $data ): array
    {
        $json = new Json($data);
        $json->offsetUnset('_id');

        if ( $this->property->count() == 0 ) {
            return $json->toArray();
        }

        return $this->validateObject($json, false)
            ->toArray();
    }
}