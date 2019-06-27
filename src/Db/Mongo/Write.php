<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Db\Mongo\Schema\Validator;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
use MongoDB\BSON\ObjectId;
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
     * @var Json
     */
    protected $fields;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Index constructor.
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->fields = new Json();
        parent::__construct($collection);
    }

    /**
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

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
            ->deleteMany($this->filter(), $this->options())
            ->getDeletedCount();
    }

    /**
     * @return bool
     */
    public function deleteOne(): bool
    {
        return (bool) $this->collection()
            ->deleteOne($this->filter(), $this->options())
            ->getDeletedCount();
    }

    /**
     * @return Json
     */
    public function deleteOneAndGet(): Json
    {
        return new Json($this->collection()
            ->findOneAndDelete($this->filter(), $this->options()), function( $k, $v )
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

        if ( Is::iterable($value) ) {
            $value = ( new Json($value, function( $k, $v )
            {
                return Collection::filterIn($k, $v);
            }) )->toArray();

        }
        elseif ( Is::scalar($value) ) {
            $value = Collection::filterIn($field, $value);
        }

        $this->fields->offsetGetOrSet($keyword)
            ->offsetSet($field, $value);

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
            ->insertOne($this->validatedInsertFields(), $this->options())
            ->getInsertedId();
    }

    /**
     * @return array
     */
    public function validatedInsertFields(): array
    {
        $set       = $this->fields->offsetGet('$set');
        $validator = new Validator($this->collection->info()
            ->toArray());

        return $validator->validateDataToInsert($set);
    }

    /**
     * @return int
     */
    public function update(): int
    {
        return (int) $this->collection()
            ->updateMany($this->filter(), $this->validatedUpdateFields(), $this->options())
            ->getModifiedCount();
    }

    /**
     * @return array
     */
    public function validatedUpdateFields(): array
    {
        $fields      = new Json($this->fields());
        $set         = $fields->offsetGet('$set');
        $setOnInsert = $fields->offsetGet('$setOnInsert');
        $validator   = new Validator($this->collection->info()
            ->toArray());

        if ($set) {
            $fields->offsetSet('$set', $validator->validateDataToUpdate($set));
        }

        if ($setOnInsert) {
            $fields->offsetSet('$setOnInsert', $validator->validateDataToUpdate($setOnInsert));
        }

        //@todo
        // cas de push et de addfield to set
            // Expr

        return $fields->toArray();
    }

    /**
     * @return Json
     */
    public function fields(): Json
    {
        return $this->fields;
    }

    /**
     * @return string|null
     */
    public function updateOrInsert(): ?string
    {
        $options = array_merge([
            'upsert' => true,
        ], $this->options());

        return (string) $this->collection()
            ->updateOne($this->filter(), $this->validatedUpdateFields(), $options)
            ->getUpsertedId();
    }

    /**
     * @return bool
     */
    public function updateOne(): bool
    {
        return (bool) $this->collection()
            ->updateOne($this->filter(), $this->validatedUpdateFields(), $this->options())
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
        ], $this->options());

        return new Json($this->collection()
            ->findOneAndUpdate($this->filter(), $this->validatedUpdateFields(), $options), function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }
}
