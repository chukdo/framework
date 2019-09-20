<?php

namespace Chukdo\Db\Elastic\Schema;

use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Helper\Str;
use Chukdo\Json\Arr;
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
                case 'type' :
                    $this->setType($value);
                    break;
                case 'copy_to' :
                    $this->setCopyTo($value);
                    break;
                case 'analyser' :
                    $this->setAnalyser($value);
                    break;
                case 'fields' :
                    $this->setFields($value);
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
     * text | keyword | int | float | boolean | date | ip | completion ...
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/mapping-types.html
     * @param string $value
     * @return Property
     */
    public function setType( string $value ): self
    {
        $this->property->offsetSet('type', $value);

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/copy-to.html
     * @param string $value
     * @return $this
     */
    public function setCopyTo( $value ): self
    {
        $this->property->offsetSet('copy_to', (array) $value);

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/analyzer.html
     * @param string $value
     * @return $this
     */
    public function setAnalyser( string $value ): self
    {
        $this->property->offsetSet('analyser', $value);

        return $this;
    }

    /**
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/multi-fields.html
     * @param array $value
     * @return $this
     */
    public function setFields( array $value ): self
    {
        $properties = $this->property->offsetGetOrSet('fields', []);

        foreach ( $value as $k => $v ) {
            $properties->offsetSet($k, new Property((array) $v, $k));
        }

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
     * @return $this|null
     */
    public function fields(): ?Property
    {
        return $this->property->offsetGet('fields');
    }

    /**
     * @param string $name
     * @return $this|null
     */
    public function get( string $name ): ?Property
    {
        if ( Str::notContain($name, '.') ) {
            return $this->properties()
                ->offsetGet($name);
        }

        $arr       = new Arr(Str::split($name, '.'));
        $firstPath = $arr->getFirstAndRemove();
        $endPath   = $arr->join('.');
        $get       = $this->properties()
            ->offsetGet($firstPath);

        if ( $get instanceof Property ) {
            return $get->get($endPath);
        }

        return null;
    }

    /**
     * @param string      $name
     * @param string|null $type
     * @param array       $options
     * @return Property
     */
    public function set( string $name, string $type = null,  array $options = [] ): Property
    {
        $property = new Property($options, $name);

        if ($type) {
            $property->setType($type);
        }

        return $this->properties()
            ->offsetGetOrSet($name, $property);
    }

    /**
     * @return JsonInterface
     */
    public function properties(): JsonInterface
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
     * @return array
     */
    public function toArray(): array
    {
        return $this->property->filterRecursive(function( $k, $v )
        {
            return $v instanceof Property
                ? $v->toArray()
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
        return $this->property->offsetGet('type');
    }

    /**
     * @return string|null
     */
    public function analyser(): ?string
    {
        return $this->property->offsetGet('analyser');
    }

    /**
     * @return string|null
     */
    public function copyTo(): ?string
    {
        return $this->property->offsetGet('copy_to');
    }
}