<?php

namespace Chukdo\Db\Mongo;

use MongoDB\Collection as MongoDbCollection;

/**
 * Mongo Update.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Update
{
    use WhereTrait;

    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->collection = $collection;
    }

    /**
     * @param array $values
     * @return Update
     */
    public function setMultiple( array $values ): self
    {
        foreach ( $values as $field => $value ) {
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param        $value
     * @return Update
     */
    public function set( string $field, $value ): self
    {
        return $this->field('set', $field, $value);
    }

    /**
     * @param string $keyword
     * @param string $field
     * @param        $value
     * @return Update
     */
    protected function field( string $keyword, string $field, $value ): self
    {
        $keyword = '$' . $keyword;

        if ( !isset($this->fields[ $keyword ]) ) {
            $this->fields[ $keyword ] = [];
        }

        $this->fields[ $keyword ][ $field ] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @return Update
     */
    public function unset( string $field ): self
    {
        return $this->field('unset', $field, '');
    }

    /**
     * @param string $field
     * @param        $value
     * @return Update
     */
    public function setOnInsert( string $field, $value ): self
    {
        return $this->field('setOnInsert', $field, $this->collection->closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Update
     */
    public function inc( string $field, int $value ): self
    {
        return $this->field('inc', $field, $value);
    }

    /**
     * @param string $field
     * @param        $value
     * @return Update
     */
    public function min( string $field, $value ): self
    {
        return $this->field('min', $field, $this->collection->closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param        $value
     * @return Update
     */
    public function max( string $field, $value ): self
    {
        return $this->field('max', $field, $this->collection->closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Update
     */
    public function mul( string $field, int $value ): self
    {
        return $this->field('mul', $field, $value);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @return Update
     */
    public function rename( string $oldName, string $newName ): self
    {
        return $this->field('rename', $oldName, $newName);
    }

    /**
     * @return int
     */
    public function update(): int
    {
        return (int) $this->collection()
            ->updateMany($this->filter(), $this->fields())
            ->getModifiedCount();
    }

    /**
     * @return MongoDbCollection
     */
    public function collection(): MongoDbCollection
    {
        return $this->collection->collection();
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        return $this->fields;
    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {
        return (string) $this->collection()
            ->updateOne($this->filter(), $this->fields(), [ 'upsert' => true ])
            ->getUpsertedId();
    }

    /**
     * @return int
     */
    public function updateOne(): int
    {
        return (int) $this->collection()
            ->updateOne($this->filter(), $this->fields())
            ->getModifiedCount();
    }
}
