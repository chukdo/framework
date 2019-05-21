<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Json\Json;
use MongoDB\Collection as MongoDbCollection;
use MongoDB\Operation\FindOneAndUpdate;

/**
 * Mongo Write.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write extends Where
{
    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @param array $values
     * @return Write
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
     * @return Write
     */
    public function set( string $field, $value ): self
    {
        return $this->field('set', $field, $value);
    }

    /**
     * @param string $keyword
     * @param string $field
     * @param        $value
     * @return Write
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
     * @return Write
     */
    public function unset( string $field ): self
    {
        return $this->field('unset', $field, '');
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function setOnInsert( string $field, $value ): self
    {
        return $this->field('setOnInsert', $field, Collection::closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Write
     */
    public function inc( string $field, int $value ): self
    {
        return $this->field('inc', $field, $value);
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function min( string $field, $value ): self
    {
        return $this->field('min', $field, Collection::closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function max( string $field, $value ): self
    {
        return $this->field('max', $field, Collection::closureIn()($field, $value));
    }

    /**
     * @param string $field
     * @param int    $value
     * @return Write
     */
    public function mul( string $field, int $value ): self
    {
        return $this->field('mul', $field, $value);
    }

    /**
     * @param string $oldName
     * @param string $newName
     * @return Write
     */
    public function rename( string $oldName, string $newName ): self
    {
        return $this->field('rename', $oldName, $newName);
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
     * @param array $values
     * @return string|null
     */
    public function insert( array $values ): ?string
    {
        return (string) $this->collection()
            ->insertOne($values)
            ->getInsertedId();
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

    /**
     * @param bool $before
     * @return Json
     */
    public function updateOneAndGet( bool $before = false ): Json
    {
        $projection = [
            'projection'     => [],
            'returnDocument' => $before
                ? FindOneAndUpdate::RETURN_DOCUMENT_BEFORE
                : FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
        ];

        return new Json($this->collection()
            ->findOneAndUpdate($this->filter(), $this->fields(), $projection), Collection::closureOut());
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return (int) $this->collection()
            ->deleteMany($this->filter())
            ->getDeletedCount();
    }

    /**
     * @return int
     */
    public function deleteOne(): int
    {
        return (int) $this->collection()
            ->deleteOne($this->filter())
            ->getDeletedCount();
    }

    /**
     * @return Json
     */
    public function deleteOneAndGet(): Json
    {
        return new Json($this->collection()
            ->findOneAndDelete($this->filter()), Collection::closureOut());
    }
}
