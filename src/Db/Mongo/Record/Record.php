<?php

namespace Chukdo\Db\Mongo\Record;

use Exception;
use DateTime;
use Chukdo\Db\Mongo\Index;
use Chukdo\Db\Mongo\MongoException;
use Chukdo\Db\Mongo\Schema\Schema;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Collection;
use Chukdo\Contracts\Json\Json as JsonInterface;
use Chukdo\Contracts\Db\Record as RecordInterface;
use MongoDB\Driver\Session as MongoSession;

/**
 * Mongo Record.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Record extends Json implements RecordInterface
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $id = null;

    /**
     * @var bool
     */
    protected $autoDateRecord = false;

    /**
     * @var bool
     */
    protected $binTrashRecord = false;

    /**
     * Record constructor.
     * @param Collection $collection
     * @param null       $data
     */
    public function __construct( Collection $collection, $data = null )
    {
        parent::__construct($data, false);
        parent::__construct($this->filterRecursive(function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        }), false);

        $this->collection = $collection;
        $this->id         = $this->offsetGet('_id');
    }

    /**
     * @param MongoSession|null $session
     * @return JsonInterface
     * @throws Exception
     */
    public function delete( MongoSession $session = null ): JsonInterface
    {
        $write = $this->collection->write()
            ->setSession($session);

        if ( ( $id = $this->id() ) !== null ) {
            $write->where('_id', '=', $id);

            $get = $write->deleteOneAndGet();

            /** Options delete to Bin */
            if ( $this->binTrashRecord ) {
                $this->collection()
                    ->mongo()
                    ->collection($this->collection()
                                     ->name() . '_bintrash')
                    ->write()
                    ->setSession($session)
                    ->setAll($get)
                    ->set('date_deleted', new DateTime())
                    ->insert();
            }

            return $get;
        }

        throw new MongoException('No ID to delete Record');
    }

    /**
     * @return string|null
     */
    public function id(): ?string
    {
        return $this->id;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * Initialise le modele en injectant le schema et les index
     */
    public function init()
    {
        $this->index()
            ->drop();
        $this->initIndex();
        $this->schema()
            ->drop();
        $this->initSchema();
    }

    /**
     * @return Index
     */
    public function index(): Index
    {
        return $this->collection()
            ->index();
    }

    /**
     * Création des index
     */
    public function initIndex()
    {
    }

    /**
     * @return Schema
     */
    public function schema(): Schema
    {
        return $this->collection()
            ->schema();
    }

    /**
     * Création des schema de validation des données
     */
    public function initSchema()
    {
    }

    /**
     * @param MongoSession|null $session
     * @return mixed|string|null
     * @throws Exception
     */
    public function save( MongoSession $session = null )
    {
        $write = $this->collection->write()
            ->setSession($session);
        $write->setAll($this->filterRecursive(function( $k, $v )
        {
            if ( !Is::RecordInterface($v) ) {
                return $v;
            }
        }));

        /** Option Auto Date */
        if ( $this->autoDateRecord ) {
            $write->setOnInsert('date_created', new DateTime())
                ->set('date_modified', new DateTime());
        }

        /** Update */
        if ( ( $id = $this->id() ) !== null ) {
            $write->where('_id', '=', $id);

            return $write->updateOrInsert();

            /** Save */
        }
        else {
            return $write->insert();
        }
    }


}