<?php namespace Chukdo\Validation;

/**
 * Validation des regles
 *
 * @package     Validation
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */

class Rules
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Rules constructor.
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }
}