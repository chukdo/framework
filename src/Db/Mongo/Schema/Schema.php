<?php

namespace Chukdo\Db\Mongo\Schema;

use Chukdo\Contracts\Db\Schema as SchemaInterface;
use Chukdo\DB\Mongo\Collection;
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
     * Schema constructor.
     *
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
        $db               = $collection->database();
        $json             = $db->server()
                               ->command( [ 'listCollections' => 1,
                                            'filter'          => [ 'name' => $collection->name() ], ], $db->name() );
        $this->property   = new Property( $json->getJson( '0.options.validator.$jsonSchema' )
                                               ->toArray() );
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $schema = [ 'validator'        => [ '$jsonSchema' => [ 'bsonType' => 'object' ], ],
                    'validationLevel'  => 'strict',
                    'validationAction' => 'error', ];
        $save   = new Json( $this->collection()
                                 ->database()
                                 ->client()
                                 ->modifyCollection( $this->collection()
                                                          ->name(), $schema ) );
        if ( $save->offsetGet( 'ok' ) === 1 ) {
            $this->property = new Property();

            return true;
        }

        return false;
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
     * @return Property
     */
    public function property(): Property
    {
        return $this->property;
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
        return $this->property()
                    ->properties();
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        $schema = [ 'validator'        => [ '$jsonSchema' => $this->toArray(), ],
                    'validationLevel'  => 'strict',
                    'validationAction' => 'error', ];
        $save   = new Json( $this->collection()
                                 ->database()
                                 ->client()
                                 ->modifyCollection( $this->collection()
                                                          ->name(), $schema ) );

        return $save->offsetGet( 'ok' ) === 1;
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