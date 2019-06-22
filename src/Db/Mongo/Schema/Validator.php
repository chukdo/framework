<?php


namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Db\Mongo\MongoException;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\Timestamp;
use DateTime;

/**
 * Data validation with Mongo Schema.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator
{
    /**
     * @var Schema
     */
    protected $schema;

    /**
     * Validator constructor.
     * @param Schema $schema
     */
    public function __construct( Schema $schema )
    {
        $this->schema = $schema;
    }

    /**
     * @param Property      $property
     * @param JsonInterface $json
     * @return JsonInterface
     */
    protected function validateProperty( Property $property, JsonInterface $json ): JsonInterface
    {
        $name = $property->name();
        $get  = $json->get($name);
        $type = $property->type();

        switch ( $type ) {
            case 'objectId' :
                $get = $this->validateObjectId($property, $get);
                break;
            case 'string' :
                $get = $this->validateString($property, $get);
                break;
            case 'int':
            case 'long':
                $get = $this->validateInt($property, $get);
                break;
            case 'decimal':
            case 'double' :
            case 'float':
                $get = $this->validatefloat($property, $get);
                break;
            case 'boolean' :
                $get = $this->validateBool($property, $get);
                break;
            case 'date' :
                $get = $this->validateDate($property, $get);
                break;
            case 'timestamp' :
                $get = $this->validateTimestamp($property, $get);
                break;
            case 'enum' :
                $get = $this->validateList($property, $get);
                break;
            case 'array':
                $get = $this->validateArray($property, $get);
                break;
            case 'object':
                $get = $this->validateObject($property, $get);
                break;
            default :
                throw new MongoException(sprintf("The field [%s] must be a valid type not [%s]", $name, $type));
        }

        $json->set($name, $get);

        return $json;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return ObjectId
     */
    protected function validateObjectId( Property $property, $data )
    {
        $name = $property->name();

        if ( $data instanceof ObjectId ) {
            return $data;
        }
        elseif ( Is::string($data) ) {
            return new ObjectId($data);
        }

        throw new MongoException(sprintf("The field [%s] must be a objectId", $name));
    }

    /**
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateString( Property $property, $data )
    {
        $name = $property->name();

        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a string", $name));
        }

        $data    = (string) $data;
        $pattern = $property->pattern();

        if ( $pattern ) {
            if ( !Str::match('/' . $pattern . '/i', $data) ) {
                throw new MongoException(sprintf("The field [%s] must be a string with pattern [%s]", $name, $pattern));
            }
        }

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return int
     */
    protected function validateInt( Property $property, $data )
    {
        $name = $property->name();

        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a int", $name));
        }

        $data = (int) $data;
        $min  = $property->min();
        $max  = $property->max();

        if ( $min ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $name, $min));
            }
        }

        if ( $max ) {
            if ( $data < $max ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $name, $max));
            }
        }

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return float
     */
    protected function validateFloat( Property $property, $data )
    {
        $name = $property->name();

        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a float", $name));
        }

        $data = (float) $data;
        $min  = $property->min();
        $max  = $property->max();

        if ( $min ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $name, $min));
            }
        }

        if ( $max ) {
            if ( $data < $max ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $name, $max));
            }
        }

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return bool
     */
    protected function validateBool( Property $property, $data )
    {
        $name = $property->name();

        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a bool", $name));
        }

        $data = (bool) $data;

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return UTCDateTime
     */
    protected function validateDate( Property $property, $data )
    {
        $name = $property->name();

        if ( $data instanceof UTCDateTime ) {
            return $data;
        }
        elseif ( $data instanceof DateTime ) {
            return new UTCDateTime($data->getTimestamp());
        }
        elseif ( Is::scalar($data) ) {
            return new UTCDateTime((int) $data);
        }

        throw new MongoException(sprintf("The field [%s] must be a date", $name));
    }

    /**
     * @param Property $property
     * @param          $data
     * @return Timestamp
     */
    protected function validateTimestamp( Property $property, $data )
    {
        $name = $property->name();

        if ( $data instanceof Timestamp ) {
            return $data;
        }
        elseif ( $data instanceof DateTime ) {
            return new Timestamp($data->getTimestamp(), 1);
        }
        elseif ( Is::scalar($data) ) {
            return new Timestamp((int) $data, 1);
        }

        throw new MongoException(sprintf("The field [%s] must be a timestamp", $name));
    }

    /**
     * @param Property $property
     * @param          $data
     * @return mixed
     */
    protected function validateList( Property $property, $data )
    {
        $name = $property->name();
        $list = $property->list();

        if ( !Is::scalar($data) || !$list->in($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a element of list [%s]", $name, implode(',', $list->toArray())));
        }

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return mixed
     */
    protected function validateArray( Property $property, $data )
    {
        $name = $property->name();

        if ( !( $data instanceof JsonInterface ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $name));
        }

        $count = $data->count();
        $min   = $property->minItems();
        $max   = $property->maxItems();

        if ( $min ) {
            if ( $count < $min ) {
                throw new MongoException(sprintf("The field [%s] must have more than [%s] items", $name, $min));
            }
        }

        if ( $max ) {
            if ( $count < $max ) {
                throw new MongoException(sprintf("The field [%s] must have less than [%s] items", $name, $max));
            }
        }

        foreach ( $property->properties() as $key => $property ) {
            $this->validateProperty($property, $data);
        }

        return $data;
    }

    /**
     * @param Property $property
     * @param          $data
     * @return mixed
     */
    protected function validateObject( Property $property, $data )
    {
        $name = $property->name();

        if ( !( $data instanceof JsonInterface ) ) {
            throw new MongoException(sprintf("The field [%s] must be a object", $name));
        }

        foreach ( $property->required() as $required ) {
            if ( $data->get($required) === null ) {
                throw new MongoException(sprintf("The field [%s] is required", $required));
            }
        }

        foreach ( $property->properties() as $key => $property ) {
            $this->validateProperty($property, $data);
        }

        return $data;
    }

    /**
     * @param JsonInterface $json
     * @return JsonInterface
     */
    public function validate( JsonInterface $json ): JsonInterface
    {
        return $this->validateProperty($this->schema, $json);
    }


}