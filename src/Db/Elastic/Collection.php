<?php

Namespace Chukdo\DB\Elastic;

use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Mongo Mongo Collect.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Collection
{
    /**
     * @var Elastic
     */
    protected $elastic;

    /**
     * @var string
     */
    protected $collection;

    /**
     * Collection constructor.
     * @param Elastic $elastic
     * @param string  $collection
     */
    public function __construct( Elastic $elastic, string $collection )
    {
        $this->elastic    = $elastic;
        $this->collection = $collection;


    }

    /**
     * @return Elastic
     */
    public function Elastic(): Elastic
    {
        return $this->elastic;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->collection;
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return new Schema($this);
    }

    /**
     * @param $data
     * @return RecordInterface
     */
    public function record( $data ): RecordInterface
    {
        try {
            $reflector = new ReflectionClass('\App\Model\\' . $this->name());

            return $reflector->newInstanceArgs([
                $this,
                $data,
            ]);

        } catch ( ReflectionException $e ) {
            return new Record($this, $data);
        }
    }

    /**
     * @return JsonInterface
     */
    public function info(): JsonInterface
    {
        $json = $this->mongo()
            ->command([
                'listCollections' => 1,
                'filter'          => [ 'name' => $this->name() ],
            ], $this->databaseName());

        return $json->get('0.options.validator.$jsonSchema', new Json());
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        $drop = $this->mongoCollection()
            ->drop();

        return $drop[ 'ok' ] == 1;
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename( string $newName ): bool
    {
        $rename = $this->mongo()
            ->command([
                'renameCollection' => $this->databaseName() . '.' . $this->name(),
                'to'               => $this->databaseName() . '.' . $newName,
            ])
            ->offsetGet('ok');

        if ( $rename == 1 ) {
            return true;
        }

        return false;
    }

    /**
     * @return Write
     */
    public function write(): Write
    {
        return new Write($this);
    }

    /**
     * @return Find
     */
    public function find(): Find
    {
        return new Find($this);
    }
}