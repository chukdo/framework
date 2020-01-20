<?php

namespace Chukdo\Db\Elastic\Schema;

use Throwable;
use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Db\Elastic\Collection;
use Chukdo\Json\Json;

/**
 * Server Schema.
 *
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Schema implements SchemaInterface
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var Property
     */
    protected Property $property;

    /**
     * Index constructor.
     *
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
        $name             = $collection->fullName();
        $info             = new Json( $collection->client()
                                                 ->indices()
                                                 ->getMapping( [ 'index' => $name ] ) );
        $properties       = $info->getJson( $name . '.mappings' )
                                 ->toArray();
        $this->property   = new Property( $properties );
    }

    /**
     * @param array $properties
     *
     * @return $this
     */
    public function setAll( array $properties ): self
    {
        $this->property()
             ->setAll( $properties );

        return $this;
    }

    /**
     * @return Property
     */
    public function property(): Property
    {
        return $this->property;
    }

    /**
     * @param string $name
     * @param null   $type
     * @param array  $options
     *
     * @return $this
     */
    public function set( string $name, $type = null, array $options = [] ): self
    {
        $this->property()
             ->set( $name, $type, $options );

        return $this;
    }

    /**
     * @return Json
     */
    public function properties(): Json
    {
        return $this->property->properties();
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        try {
            $this->collection()
                 ->client()
                 ->indices()
                 ->putMapping( [
                                   'index' => $this->collection()
                                                   ->fullName(),
                                   'body'  => [],
                               ] );

            return true;
        }
        catch ( Throwable $e ) {
            return false;
        }
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @param string $name
     *
     * @return Property|null
     */
    public function get( string $name ): ?Property
    {
        return $this->property()
                    ->get( $name );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function unset( string $name ): self
    {
        $this->property()
             ->unset( $name );

        return $this;
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $save = new Json( $this->collection()
                               ->client()
                               ->indices()
                               ->putMapping( [
                                                 'index' => $this->collection()
                                                                 ->name(),
                                                 'body'  => $this->toArray(),
                                             ] ) );

        return $save->offsetGet( 'acknowledged' ) === 1;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return $this->property()
                    ->toArray();
    }
}