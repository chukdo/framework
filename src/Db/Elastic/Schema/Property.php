<?php

namespace Chukdo\Db\Elastic\Schema;

use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Iterate;
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
    public function __construct( Array $property = [], string $name = null )
    {
        $this->name     = $name;
        $this->property = new Json();
        foreach ( $property as $key => $value ) {
            switch ( $key ) {
                case 'properties' :
                    $this->setProperties( (array) $value );
                    break;
                case 'type' :
                    $this->setType( $value );
                    break;
                case 'copy_to' :
                    $this->setCopyTo( $value );
                    break;
                case 'analyser' :
                    $this->setAnalyser( $value );
                    break;
                case 'fields' :
                    $this->setFields( $value );
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
            $properties->offsetSet( $k, new Property( (array) $v, $k ) );
        }

        return $this;
    }

    /**
     * text | keyword | int | float | boolean | date | ip | completion ...
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
     *
     * @param string $value
     *
     * @return Property
     */
    public function setType( $value ): Property
    {
        /** Intercompatiblité propriété Mongo|Elastic */
        if ( $value === 'string' ) {
            $value = 'keyword';
        }
        $this->property->offsetSet( 'type', $value );

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/copy-to.html
     * @param string $value
     *
     * @return Property
     */
    public function setCopyTo( $value ): Property
    {
        $this->property->offsetSet( 'copy_to', (array) $value );

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analyzer.html
     * @param string $value
     *
     * @return Property
     */
    public function setAnalyser( string $value ): Property
    {
        $this->property->offsetSet( 'analyser', $value );

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @param array $value
     *
     * @return Property
     */
    public function setFields( array $value ): Property
    {
        $properties = $this->property->offsetGetOrSet( 'fields' );
        foreach ( $value as $k => $v ) {
            $properties->offsetSet( $k, new Property( (array) $v, $k ) );
        }

        return $this;
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
     * @param mixed  $type
     * @param array  $options
     *
     * @return Property
     */
    public function set( string $name, $type = null, array $options = [] ): Property
    {
        $property = new Property( $options, $name );
        if ( Is::string( $type ) ) {
            $property->setType( $type );
        }
        else {
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
        return $this->property->filterRecursive( fn( $k, $v ) => $v instanceof Property
            ? $v->toArray()
            : $v )
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
        return $this->property->offsetGet( 'type' );
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
     * @return int
     */
    public function count(): int
    {
        return $this->property->count();
    }

    /**
     * @return Property|null
     */
    public function fields(): ?Property
    {
        return $this->property->offsetGet( 'fields' );
    }

    /**
     * @return string|null
     */
    public function analyser(): ?string
    {
        return $this->property->offsetGet( 'analyser' );
    }

    /**
     * @return string|null
     */
    public function copyTo(): ?string
    {
        return $this->property->offsetGet( 'copy_to' );
    }
}