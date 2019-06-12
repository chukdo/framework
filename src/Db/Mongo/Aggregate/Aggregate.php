<?php

namespace Chukdo\Db\Mongo\Aggregate;

/**
 * Mongo Aggregate Group.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
Class Aggregate
{
    /**
     * @var array
     */
    protected $pipe = [];

    /**
     * Aggregate constructor.
     */
    public function __construct()
    {

    }

    /**
     * @param $expression
     * @return Group
     */
    public function group( $expression ): Group
    {
        return $this->pipe[] = new Group($expression);
    }

    public function match()
    {

    }

    public function limit()
    {

    }

    public function sort()
    {

    }

    public function project()
    {

    }
}