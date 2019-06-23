<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Db\Mongo\Schema\Validator;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
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
    use Session;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @return Write
     */
    public function bypassValidation(): self
    {
        $this->options[ 'bypassDocumentValidation' ] = true;

        return $this;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return (int) $this->collection()
            ->deleteMany($this->filter(), $this->options)
            ->getDeletedCount();
    }

    /**
     * @return bool
     */
    public function deleteOne(): bool
    {
        return (bool) $this->collection()
            ->deleteOne($this->filter(), $this->options)
            ->getDeletedCount();
    }

    /**
     * @return Json
     */
    public function deleteOneAndGet(): Json
    {
        return new Json($this->collection()
            ->findOneAndDelete($this->filter(), $this->options), function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }

    /**
     * @param iterable $values
     * @return Write
     */
    public function setMultiple( iterable $values ): self
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

        if ( Is::iterable($value) ) {
            $value = ( new Json($value, function( $k, $v )
            {
                return Collection::filterIn($k, $v);
            }) )->toArray();

        }
        elseif ( Is::scalar($value) ) {
            $value = Collection::filterIn($field, $value);
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
        return $this->field('setOnInsert', $field, $value);
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
        return $this->field('min', $field, $value);
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function max( string $field, $value ): self
    {
        return $this->field('max', $field, $value);
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
     * @return string|null
     */
    public function insert(): ?string
    {
        return (string) $this->collection()
            ->insertOne($this->fields('set'), $this->options)
            ->getInsertedId();
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function fields( string $type = null ): array
    {
        $fields = $this->fields;

        if ( $type ) {
            return isset($fields[ '$' . $type ])
                ? $fields[ '$' . $type ]
                : [];
        }

        return $fields;
    }

    /**
     * @param array|Json $data
     * @param bool $insert
     * @return array
     */
    public function validate($data, bool $insert = true): array
    {
        $validator = new Validator($this->collection->info()->toArray());

        return $validator->validate($data, $insert);

        // validateUpdateFields()
        // validateInsertFields()
        // fields > json
        // unset > check required
        // set > validate + required if insert
        // setOnInsert > validate
    }

    /**
     * @return int
     */
    public function update(): int
    {
        return (int) $this->collection()
            ->updateMany($this->filter(), $this->fields(), $this->options)
            ->getModifiedCount();
    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {
        $options = array_merge([
            'upsert' => true,
        ], $this->options);

        return (string) $this->collection()
            ->updateOne($this->filter(), $this->fields(), $options)
            ->getUpsertedId();
    }

    /**
     * @return bool
     */
    public function updateOne(): bool
    {
        return (bool) $this->collection()
            ->updateOne($this->filter(), $this->fields(), $this->options)
            ->getModifiedCount();
    }

    /**
     * @param bool $before
     * @return Json
     */
    public function updateOneAndGet( bool $before = false ): Json
    {
        $options = array_merge([
            'projection'     => [],
            'returnDocument' => $before
                ? FindOneAndUpdate::RETURN_DOCUMENT_BEFORE
                : FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
        ], $this->options);

        return new Json($this->collection()
            ->findOneAndUpdate($this->filter(), $this->fields(), $options), function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }
}
