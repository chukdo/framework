<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Mongo Aggregate Group.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Group
{
    /**
     * @var array
     */
    protected $group = [];

    /**
     * Group constructor.
     * @param $expression
     */
    public function __construct( $expression )
    {
        $this->group['_id'] = Expression::parseExpression($expression);
    }

    /**
     * @param string $field
     * @param        $expression
     * @return Group
     */
    public function calculate(string $field, $expression): self
    {
        $this->group[$field] = Expression::parseExpression($expression);

        return $this;
    }

    /**
     * @return array
     */
    public function projection(): array
    {
        return $this->group;
    }
}