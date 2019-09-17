<?php

namespace Chukdo\Db\Mongo;

use Chukdo\Contracts\Db\Collection as CollectionInterface;
use Chukdo\Contracts\Db\Write as WriteInterface;
use Chukdo\Db\Mongo\Schema\Validator;
use Chukdo\Helper\Is;
use Chukdo\Json\Json;
use MongoDB\BSON\Regex;
use MongoDB\Driver\Session as MongoSession;
use MongoDB\Operation\FindOneAndUpdate;
use Chukdo\Contracts\Json\Json as JsonInterface;

/**
 * Server Write.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Write extends Where implements WriteInterface
{
    /**
     * @var CollectionInterface
     */
    protected $collection;

    /**
     * @var Json
     */
    protected $fields;

    /**
     * @var array
     */
    protected $options = [];


    /**
     * Where constructor.
     * @param CollectionInterface $collection
     */
    public function __construct( CollectionInterface $collection )
    {
        $this->fields     = new Json();
        $this->collection = $collection;
    }

    /**
     * @return bool
     */
    public function hasSession(): bool
    {
        return isset($this->options[ 'session' ]);
    }

    /**
     * @return MongoSession|null
     */
    public function getSession(): ?MongoSession
    {
        if ( isset($this->options[ 'session' ]) ) {
            return $this->options[ 'session' ];
        }

        return null;
    }

    /**
     * @param MongoSession|null $session
     * @return Write
     */
    public function setSession( MongoSession $session = null ): self
    {
        if ( $session ) {
            if ( isset($this->options[ 'session' ]) ) {
                $this->options[ 'session' ]->endSession();
            }

            $this->options[ 'session' ] = $session;
        }

        return $this;
    }

    /**
     * @return Write
     */
    public function startTransaction(): self
    {
        $this->session()
            ->startTransaction([]);

        return $this;
    }

    /**
     * @return MongoSession
     */
    public function session(): MongoSession
    {
        if ( isset($this->options[ 'session' ]) ) {
            return $this->options[ 'session' ];
        }
        else {
            $mongo = $this->collection()
                ->database()
                ->server()
                ->client();

            return $this->options[ 'session' ] = $mongo->startSession();
        }
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->collection;
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return (int) $this->collection()
            ->client()
            ->deleteMany($this->filter(), $this->options())
            ->getDeletedCount();
    }

    /**
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return bool
     */
    public function deleteOne(): bool
    {
        return (bool) $this->collection()
            ->client()
            ->deleteOne($this->filter(), $this->options())
            ->getDeletedCount();
    }

    /**
     * @return JsonInterface
     */
    public function deleteOneAndGet(): JsonInterface
    {
        $json = new Json($this->collection()
            ->client()
            ->findOneAndDelete($this->filter(), $this->options()));

        return $json->filterRecursive(function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }

    /**
     * @return string|null
     */
    public function insert(): ?string
    {
        return (string) $this->collection()
            ->client()
            ->insertOne($this->validatedInsertFields(), $this->options())
            ->getInsertedId();
    }

    /**
     * @return array
     */
    public function validatedInsertFields(): array
    {
        $set       = $this->fields->offsetGet('$set');
        $validator = new Validator($this->collection()
            ->schema()
            ->property());

        return $validator->validateDataToInsert($set);
    }

    /**
     * @return Write
     */
    public function commitTransaction(): self
    {
        $this->session()
            ->commitTransaction();

        return $this;
    }

    /**
     * @return Write
     */
    public function abortTransaction(): self
    {
        $this->session()
            ->abortTransaction();

        return $this;
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
     * @param string $field
     * @return Write
     */
    public function pop( string $field ): self
    {
        return $this->field('pop', $field, 1);
    }    /**
     * @param iterable $values
     * @return Write
     */
    public function setAll( iterable $values ): self
    {
        foreach ( $values as $field => $value ) {
            $this->set($field, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @return Write
     */
    public function shift( string $field ): self
    {
        return $this->field('pop', $field, -1);
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function addToSet( string $field, $value ): self
    {
        return $this->field('addToSet', $field, $value);
    }

    /**
     * @param string $field
     * @param array  $values
     * @return Write
     */
    public function addToSetAll( string $field, array $values ): self
    {
        return $this->field('addToSet', $field, [
            '$each' => $values,
        ]);
    }    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function set( string $field, $value ): self
    {
        return $this->field('set', $field, $value);
    }

    /**
     * @param string $field
     * @param string $operator
     * @param        $value
     * @param null   $value2
     * @return Write
     */
    public function pull( string $field, string $operator, $value, $value2 = null ): self
    {
        switch ( $operator ) {
            case '=' :
                return $this->field('pull', $field, $value);
                break;
            case '!=' :
                return $this->field('pull', $field, [
                    '$ne' => $value,
                ]);
                break;
            case '>' :
                return $this->field('pull', $field, [
                    '$gt' => $value,
                ]);
                break;
            case '>=':
                return $this->field('pull', $field, [
                    '$gte' => $value,
                ]);
                break;
            case '<':
                return $this->field('pull', $field, [
                    '$lt' => $value,
                ]);
                break;
            case '<=':
                return $this->field('pull', $field, [
                    '$lte' => $value,
                ]);
                break;
            case '<>' :
                return $this->field('pull', $field, [
                    '$gt' => $value,
                    '$lt' => $value2,
                ]);
            case '<=>' :
                return $this->field('pull', $field, [
                    '$gte' => $value,
                    '$lte' => $value2,
                ]);
            case 'in':
                return $this->field('pull', $field, [
                    '$in' => $value,
                ]);
                break;
            case '!in':
                return $this->field('pull', $field, [
                    '$nin' => $value,
                ]);
                break;
            case 'type':
                return $this->field('pull', $field, [
                    '$type' => $value,
                ]);
                break;
            case 'regex':
                return $this->field('pull', $field, [
                    '$regex' => new Regex($value, $value2
                        ?: 'i'),
                ]);
                break;
            case 'match':
                return $this->field('pull', $field, [
                ]);
                break;
            case 'all':
                return $this->field('pullAll', $field, $value);
                break;
            default :
                throw new MongoException(sprintf("Unknown operator [%s]", $operator));

        }
    }

    /**
     * @param string $field
     * @param        $value
     * @return Write
     */
    public function push( string $field, $value ): self
    {
        return $this->field('push', $field, $value);
    }

    /**
     * @param string      $field
     * @param array       $values
     * @param int|null    $position
     * @param int|null    $slice
     * @param string|null $orderby
     * @param string      $sort
     * @return Write
     */
    public function pushAll( string $field, array $values, int $position = null, int $slice = null, string $orderby = null, string $sort = 'ASC' ): self
    {
        $value = [
            '$each' => $values,
        ];

        if ( $position !== null ) {
            $value[ '$position' ] = $position;
        }

        if ( $slice !== null ) {
            $value[ '$slice' ] = $slice;
        }

        if ( $orderby !== null ) {
            $value[ '$sort' ] = [
                $orderby => $sort === 'asc' || $sort === 'ASC'
                    ? 1
                    : -1,
            ];
        }

        return $this->field('push', $field, $value);
    }    /**
     * @param string $keyword
     * @param string $field
     * @param        $value
     * @return Write
     */
    protected function field( string $keyword, string $field, $value ): self
    {
        $keyword = '$' . $keyword;

        if ( Is::iterable($value) ) {
            $values = [];

            foreach ( $value as $k => $v ) {
                $values[ $k ] = Collection::filterIn($k, $v);
            }

            $value = $values;
        }
        else {
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
     * @return int
     */
    public function update(): int
    {
        return (int) $this->collection()
            ->client()
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
        $push        = $fields->offsetGet('$push');
        $addToSet    = $fields->offsetGet('$addToSet');
        $validator   = new Validator($this->collection->schema()
            ->property());

        if ( $set ) {
            $fields->offsetSet('$set', $validator->validateDataToUpdate($set));
        }

        if ( $setOnInsert ) {
            $fields->offsetSet('$setOnInsert', $validator->validateDataToUpdate($setOnInsert));
        }

        if ( $push ) {
            if ( $each = $push->offsetGet('$each') ) {
                $push->offsetSet('$each', $validator->validateDataToUpdate($each));
            }
            else {
                $fields->offsetSet('$push', $validator->validateDataToUpdate($push));
            }
        }

        if ( $addToSet ) {
            if ( $each = $addToSet->offsetGet('$each') ) {
                $addToSet->offsetSet('$each', $validator->validateDataToUpdate($each));
            }
            else {
                $fields->offsetSet('$addToSet', $validator->validateDataToUpdate($addToSet));
            }
        }

        return $fields->toArray();
    }

    /**
     * @return JsonInterface
     */
    public function fields(): JsonInterface
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
            ->client()
            ->updateOne($this->filter(), $this->validatedUpdateFields(), $options)
            ->getUpsertedId();
    }

    /**
     * @return bool
     */
    public function updateOne(): bool
    {
        return (bool) $this->collection()
            ->client()
            ->updateOne($this->filter(), $this->validatedUpdateFields(), $this->options())
            ->getModifiedCount();
    }

    /**
     * @param bool $before
     * @return JsonInterface
     */
    public function updateOneAndGet( bool $before = false ): JsonInterface
    {
        $options = array_merge([
            'projection'     => [],
            'returnDocument' => $before
                ? FindOneAndUpdate::RETURN_DOCUMENT_BEFORE
                : FindOneAndUpdate::RETURN_DOCUMENT_AFTER,
        ], $this->options());

        $json = new Json($this->collection()
            ->client()
            ->findOneAndUpdate($this->filter(), $this->validatedUpdateFields(), $options));

        return $json->filterRecursive(function( $k, $v )
        {
            return Collection::filterOut($k, $v);
        });
    }
}
