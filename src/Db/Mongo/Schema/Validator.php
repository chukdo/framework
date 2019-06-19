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
     * @param JsonInterface $json
     * @return JsonInterface
     */
    public function validate( JsonInterface $json ): JsonInterface
    {
        foreach ( $this->schema()
            ->required() as $required ) {
            if ( $json->get($required) === null ) {
                throw new MongoException(sprintf("The field [%s] is required", $required));
            }
        }

        foreach ( $this->schema()
            ->properties() as $key => $value ) {
            $get  = $json->get($key);
            $type = $value->bsonType();

            switch ( $type ) {
                case 'objectId' :
                    $get = $this->validateObjectId($key, $value, $get);
                    break;
                case 'string' :
                    $get = $this->validateString($key, $value, $get);
                    break;
                case 'int':
                case 'long':
                    $get = $this->validateInt($key, $value, $get);
                    break;
                case 'decimal':
                case 'double' :
                case 'float':
                    $get = $this->validatefloat($key, $value, $get);
                    break;
                case 'boolean' :
                    $get = $this->validateBool($key, $value, $get);
                    break;
                case 'date' :
                    $get = $this->validateDate($key, $value, $get);
                    break;
                case 'timestamp' :
                    $get = $this->validateTimestamp($key, $value, $get);
                    break;
                case 'enum' :
                    $get = $this->validateList($key, $value, $get);
                    break;
                case 'array':
                    // loop
                    break;
                case 'object':
                    // ?!
                    break;
            }

            $json->set($key, $get);
        }

        return $json;
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->schema;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateObjectId( string $key, Property $property, $data )
    {
        if ( $data instanceof ObjectId ) {
            return $data;
        }
        elseif ( Is::string($data) ) {
            return new ObjectId($data);
        }

        throw new MongoException(sprintf("The field [%s] must be a objectId", $key));
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateString( string $key, Property $property, $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a string", $key));
        }

        $data    = (string) $data;
        $pattern = $property->pattern();

        if ( $pattern ) {
            if ( !Str::match('/' . $pattern . '/i', $data) ) {
                throw new MongoException(sprintf("The field [%s] must be a string", $key));
            }
        }

        return $data;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    public function validateInt( string $key, Property $property, $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a int", $key));
        }

        $data = (int) $data;
        $min  = $property->min();
        $max  = $property->max();

        if ( $min ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $key, $min));
            }
        }

        if ( $max ) {
            if ( $data < $max ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $key, $max));
            }
        }

        return $data;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    public function validateFloat( string $key, Property $property, $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a float", $key));
        }

        $data = (float) $data;
        $min  = $property->min();
        $max  = $property->max();

        if ( $min ) {
            if ( $data < $min ) {
                throw new MongoException(sprintf("The field [%s] must be lower than [%s]", $key, $min));
            }
        }

        if ( $max ) {
            if ( $data < $max ) {
                throw new MongoException(sprintf("The field [%s] must be greater than [%s]", $key, $max));
            }
        }

        return $data;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    public function validateBool( string $key, Property $property, $data )
    {
        if ( !Is::scalar($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a bool", $key));
        }

        $data = (bool) $data;

        return $data;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateList( string $key, Property $property, $data )
    {
        $list = $property->list();

        if ( !Is::scalar($data) || !$list->in($data) ) {
            throw new MongoException(sprintf("The field [%s] must be a element of list [%s]", $key, implode(',', $list->toArray())));
        }

        return $data;
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateDate( string $key, Property $property, $data )
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

        throw new MongoException(sprintf("The field [%s] must be a date", $key));
    }

    /**
     * @param string   $key
     * @param Property $property
     * @param          $data
     * @return string
     */
    protected function validateTimestamp( string $key, Property $property, $data )
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

        throw new MongoException(sprintf("The field [%s] must be a timestamp", $key));
    }


}