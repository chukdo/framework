<?php

Namespace Chukdo\DB\Elastic;

use Chukdo\Db\Elastic\Schema\Schema;
use Chukdo\Helper\Is;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Contracts\Json\Json as JsonInterface;
use DateTime;
use Elasticsearch\Client;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Elasticsearch\Namespaces\IndicesNamespace;

/**
 * Server Server Collect.
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
     * @param string|null $field
     * @param             $value
     * @return DateTime
     * @throws \Exception
     */
    public static function filterOut( ?string $field, $value )
    {
        if ( Str::contain($field, 'date') ) {
            return (new DateTime())->setTimestamp(1000 * (int) (string) $value);
        }

        return $value;
    }

    /**
     * @param string|null $field
     * @param             $value
     * @return mixed
     */
    public static function filterIn( ?string $field, $value )
    {
        if ( $value instanceof DateTime ) {
            $value = $value->getTimestamp() * 1000;
        }
        elseif ( Str::contain($field, 'date') && Is::scalar($value) ) {
            $value = 1000 * (int) $value;
        }

        return $value;
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
     * @return string
     */
    public function name(): string
    {
        return $this->collection;
    }

    /**
     * @return JsonInterface
     */
    public function stat(): JsonInterface
    {
        $stats = new Json($this->indices()
            ->stats([ 'index' => $this->name() ]));

        return $stats->get('indices.' . $this->name(), new Json());
    }

    /**
     * @return IndicesNamespace
     */
    public function indices(): IndicesNamespace
    {
        return $this->client()
            ->indices();
    }

    /**
     * @return Client
     */
    public function client(): Client
    {
        return $this->elastic()
            ->client();
    }

    /**
     * @return Elastic
     */
    public function elastic(): Elastic
    {
        return $this->elastic;
    }

    /**
     * @return JsonInterface
     */
    public function properties(): JsonInterface
    {
        $info = new Json($this->indices()
            ->getMapping([ 'index' => $this->name() ]));

        return $info->get($this->name() . '.mappings', new Json());
    }

    /**
     * @return bool
     */
    public function drop(): bool
    {
        try {
            $this->elastic()
                ->client()
                ->indices()
                ->delete([ 'index' => $this->name() ]);

            return true;
        } catch ( Missing404Exception $e ) {
            return false;
        }
    }

    /**
     * @param string $newName
     * @return bool
     */
    public function rename( string $newName ): bool
    {
        $this->indices()
            ->delete([ 'index' => $newName ]);

        // getThisSchema
        // createCollection newCollection
        // setThisSchema to newCollection
        // reIndex

        $this->elastic()
            ->client()
            ->reindex([
                'source' => [ 'index' => $this->collection ],
                'dest'   => [ 'index' => $newName ],
            ]);

        return true;
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