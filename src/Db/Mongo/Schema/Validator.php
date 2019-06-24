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
 * Mongo Schema properties validator.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator extends Property
{
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
            throw new MongoException(sprintf("The field [%s] must be a object", $this->name()));
        }

        $lockMessage = $this->name()
            ? sprintf("The field [%s] not allow additional properties", $this->name())
            : "No additional properties allowed for object";

        /** Insert */
        if ( $insert ) {
            if ( $this->locked() ) {
                if ( $json->diff($this->properties())
                         ->count() > 0 ) {
                    throw new MongoException($lockMessage);
                }
            }

            foreach ( $this->properties() as $key => $property ) {
                if ( $get = $json->offsetGet($key) ) {
                    $json->offsetSet($key, $property->validateProperty($get, $insert));
                }
                elseif ( $this->isRequired($key) ) {
                    throw new MongoException(sprintf("The field [%s] is required", $key));
                }
            }
        }

        /** Update */
        else {
            foreach ( $json as $key => $value ) {
                if ( $property = $this->properties()
                    ->offsetGet($key) ) {
                    $json->offsetSet($key, $property->validateProperty($value, false));
                }
                elseif ( $this->locked() ) {
                    throw new MongoException($lockMessage);
                }
            }
        }

        return $json;
    }

    /**
     * @param array|Json $data
     * @return array
     */
    public function validateDataToUpdate( $data ): array
    {
        $json = new Json($data);

        if ( $this->property->count() == 0 ) {
            return $json->toArray();
        }

        return $this->validateObject($json, false)
            ->toArray();
    }

    /**
     * @param      $json
     * @param bool $insert
     * @return mixed
     */
    protected function validateArray( $json, bool $insert = true )
    {
        if ( !( $json instanceof Json ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $this->name()));
        }

        if ( $insert ) {
            $this->checkMinItems($json);
            $this->checkMaxItems($json);
        }

        if ( $items = $this->items() ) {
            foreach ( $json as $key => $value ) {
                $json->offsetSet($key, $items->validateProperty($value, $insert));
            }
        }

        return $json;
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
     * @param      $data
     * @param bool $insert
     * @return mixed
     */
    public function validateProperty( $data, bool $insert = true )
    {
        switch ( $this->type() ) {
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
                $enum = $this->property->offsetGet('enum');

                if ( $enum ) {
                    $validatedData = $this->validateList($data);
                }
                else {
                    throw new MongoException(sprintf("The field [%s] must be a valid type not [%s]", $this->name(), $this->type()));
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
}