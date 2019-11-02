<?php

namespace Chukdo\Db\Elastic\Schema;

use Throwable;
use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Property as PropertyInterface;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\DB\Elastic\Collection;
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
    protected $collection;

    /**
     * @var Property
     */
    protected $property;

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
     * @return SchemaInterface
     */
    public function setAll( array $properties ): SchemaInterface
    {
        $this->property()
             ->setAll( $properties );

        return $this;
    }

    /**
     * @return Property
     */
    public function property(): PropertyInterface
    {
        return $this->property;
    }

    /**
     * @param string $name
     * @param null   $type
     * @param array  $options
     *
     * @return SchemaInterface
     */
    public function set( string $name, $type = null, array $options = [] ): SchemaInterface
    {
        $this->property()
             ->set( $name, $type, $options );

        return $this;
    }

    /**
     * @return JsonInterface
     */
    public function properties(): JsonInterface
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
                 ->putMapping( [ 'index' => $this->collection()
                                                 ->fullName(),
                                 'body'  => [], ] );

            return true;
        } catch ( Throwable $e ) {
            return false;
        }
    }

    /**
     * @return CollectionInterface
     */
    public function collection(): CollectionInterface
    {
        return $this->collection;
    }

    /**
     * @param string $name
     *
     * @return Property|null
     */
    public function get( string $name ): ?PropertyInterface
    {
        return $this->property()
                    ->get( $name );
    }

    /**
     * @param string $name
     *
     * @return SchemaInterface
     */
    public function unset( string $name ): SchemaInterface
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
                               ->putMapping( [ 'index' => $this->collection()
                                                               ->name(),
                                               'body'  => $this->toArray(), ] ) );

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