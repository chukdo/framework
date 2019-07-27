<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Helper\Arr;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;

/**
 * Mongo Link .
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Link
{
    /**
     * @var Database
     */
    protected $database;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var string
     */
    protected $field = null;

    /**
     * @var string
     */
    protected $linked = null;

    /**
     * @var array
     */
    protected $with = [];

    /**
     * @var array
     */
    protected $without = [];

    /**
     * Link constructor.
     * @param Database $database
     * @param string   $field db._collection ou _collection = _id of collection
     */
    public function __construct( Database $database, string $field )
    {
        $this->database = $database;
        $dbName         = $database->name();

        list($db, $field) = array_pad(explode('.', $field), -2, $dbName);

        if ( !Str::match('/^_[a-z0-9]+$/i', $field) ) {
            throw new MongoException(sprintf('Field [%s] has not a valid format.', $field));
        }

        if ( $db != $dbName ) {
            $this->database = $database->mongo()
                ->database($db);
        }

        $this->collection = $this->database->collection(substr($field, 1));
        $this->field      = $field;
    }

    /**
     * @param string|null $linked
     * @return Link
     */
    public function setLinkedName( string $linked = null ): self
    {
        $this->linked = $linked;

        return $this;
    }

    /**
     * @return string
     */
    public function getLinkedName(): string
    {
        return $this->linked
            ?: 'linked' . $this->field;
    }

    /**
     * @param mixed ...$fields
     * @return Link
     */
    public function with( ... $fields ): self
    {
        $this->with = Arr::spreadArgs($fields);

        return $this;
    }

    /**
     * @param mixed ...$fields
     * @return Link
     */
    public function without( ... $fields ): self
    {
        $this->without = Arr::spreadArgs($fields);

        return $this;
    }

    /**
     * @param Json $json
     * @return Json
     */
    public function hydrate( Json $json ): Json
    {
        return $this->hydrateIds($json, $this->findIds($this->extractIds($json)));
    }

    /**
     * @param Json $json
     * @param Json $find
     * @return Json
     */
    protected function hydrateIds( Json $json, Json $find ): Json
    {
        foreach ( $json as $key => $value ) {
            if ( $key === $this->field ) {

                /** Multiple ids */
                if ( $value instanceof Json ) {
                    $list = [];

                    foreach ( (array) $value as $id ) {
                        if ( $get = $find->offsetGet($id) ) {
                            $list[] = $get;
                        }
                    }

                    if (!empty($list)) {
                        $json->offsetSet($this->getLinkedName(), $list);
                    }
                }

                /** Single id */
                else {
                    if ( $get = $find->offsetGet($value) ) {
                        $json->offsetSet($this->getLinkedName(), $get);
                    }
                }
            }
            elseif ( $value instanceof Json ) {
                $this->hydrateIds($value, $find);
            }
        }

        return $json;
    }

    /**
     * @param array $ids
     * @return Json
     */
    protected function findIds( array $ids ): Json
    {
        $find = new Find($this->collection);

        return $find->with($this->with)
            ->without($this->without)
            ->where('_id', 'in', $ids)
            ->all(true);
    }

    /**
     * @param Json $json
     * @return array
     */
    protected function extractIds( Json $json ): array
    {
        $extractIds = [];

        foreach ( $json as $key => $value ) {
            if ( $key === $this->field ) {
                $extractIds = array_merge($extractIds, (array) $value);
            }
            elseif ( $value instanceof Json ) {
                $extractIds = array_merge($extractIds, $this->extractIds($value));
            }
        }

        return $extractIds;
    }
}