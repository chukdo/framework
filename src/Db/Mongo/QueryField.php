<?php

Namespace Chukdo\DB\Mongo;

/**
 * QueryBuilder Field Builder.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class QueryField
{
    /**
     * @var array
     */
    protected $field = [];

    /**
     * QueryField constructor.
     */
    public function __construct(  )
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function eq( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function neq( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function gt( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function gte( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function lt( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param        $value
     * @return QueryField
     */
    public function lte( string $field, $value ): self
    {

    }

    /**
     * @param string $field
     * @param array  $in
     * @return QueryField
     */
    public function in( string $field, array $in ): self
    {

    }

    /**
     * @param string $field
     * @param array  $in
     * @return QueryField
     */
    public function nin( string $field, array $in ): self
    {

    }

    /**
     * @param bool $exists
     * @return QueryField
     */
    public function exists(bool $exists): self
    {

    }

    /**
     * @param string $type
     * @return QueryField
     */
    public function type(string $type): self
    {

    }

    /**
     * @param int $divisor
     * @param int $remainder
     * @return QueryField
     */
    public function mod(int $divisor, int $remainder): self
    {

    }

    public function regex(string $pattern): self
    {

    }

    /**
     * @param string $field
     * @return int
     */
    public function inc( string $field ): int
    {

    }

    /**
     * @param string $field
     * @return int
     */
    public function dec( string $field ): int
    {

    }

    /**
     * @param array $values
     * @return QueryField
     */
    public function max( array $values ): self
    {

    }

    /**
     * @param array $values
     * @return QueryField
     */
    public function min( array $values ): self
    {

    }

    /**
     * @param string $newName
     * @return QueryField
     */
    public function rename( string $newName ): self
    {

    }
}