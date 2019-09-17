<?php

namespace Chukdo\Db\Mongo\Record;

use Exception;
use DateTime;
use Chukdo\Db\Mongo\MongoException;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
use Chukdo\Db\Mongo\Collection;
use Chukdo\Contracts\Db\Record as RecordInterface;
use MongoDB\Driver\Session as MongoSession;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Server Record.
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
     * @var string|null
     */
    protected $versioningCollection = null;

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
     * @return Record
     * @throws Exception
     */
    public function delete( MongoSession $session = null ): RecordInterface
    {
        $write = $this->collection()->write()
            ->setSession($session);

        if ( ( $id = $this->id() ) !== null ) {
            $write->where('_id', '=', $id);

            $write->deleteOne();

            return $this;
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
     * @param string            $collection
     * @param MongoSession|null $session
     * @return Record
     * @throws Exception
     */
    public function moveTo( string $collection, MongoSession $session = null ): RecordInterface
    {
        $write = $this->collection()->write()
            ->setSession($session);

        if ( ( $id = $this->id() ) !== null ) {
            $write->where('_id', '=', $id);

            $this->collection()
                ->database()
                ->collection($collection)
                ->write()
                ->setSession($session)
                ->setAll($write->deleteOneAndGet())
                ->set('date_archived', new DateTime())
                ->insert();

            return $this;
        }

        throw new MongoException('No ID to delete Record');
    }

    /**
     * @return JsonInterface
     */
    public function record(): JsonInterface
    {
        return $this->filterRecursive(function( $k, $v )
        {
            return !Is::RecordInterface($v)
                ? $v
                : null;
        });
    }

    /**
     * @param MongoSession|null $session
     * @return Record
     * @throws Exception
     */
    public function save( MongoSession $session = null ): RecordInterface
    {
        $insert = false;
        $write  = $this->collection()->write()
            ->setSession($session)
            ->setAll($this->record());

        /** Option Auto Date */
        if ( $this->autoDateRecord ) {
            $write->setOnInsert('date_created', new DateTime())
                ->set('date_modified', new DateTime());
        }

        /** Insert */
        if ( $this->id() === null ) {
            $insert   = true;
            $this->id = $write->insert();
            $this->offsetSet('_id', $this->id());
        }

        /** Update */
        else {
            $write->where('_id', '=', $this->id())
                ->updateOne();
        }

        /** Option Versioning */
        if ( $this->versioningCollection ) {
            $this->versionning($session, $insert
                ? $this->record()
                : $this->collection()
                    ->find()
                    ->where('_id', '=', $this->id())
                    ->one());
        }

        return $this;
    }

    /**
     * @param MongoSession|null $session
     * @param JsonInterface     $record
     * @return Record
     * @throws Exception
     */
    protected function versionning( ?MongoSession $session, JsonInterface $record ): RecordInterface
    {
        $db = $this->collection()
            ->database();

        if ( $session && $this->versioningCollection && !$db->collectionExist($this->versioningCollection) ) {
            $session->abortTransaction();
            throw new MongoException(sprintf('Aborting transaction, Versioning collection [%s] no exist', $this->versioningCollection));
        }

        $db->collection($this->versioningCollection)
            ->write()
            ->setSession($session)
            ->setAll($record)
            ->set('date_versioning', new DateTime())
            ->insert();

        return $this;
    }
}