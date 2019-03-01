<?php namespace Chukdo\Validation;

use Chukdo\Http\Input;
use Chukdo\Json\JsonInput;

/**
 * Validation de données
 *
 * @package     Validation
 * @version 	1.0.0
 * @copyright 	licence MIT, Copyright (C) 2019 Domingo
 * @since 		08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */

class Validator
{
    /**
     * @var JsonInput
     */
    protected $inputs;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Validator constructor.
     * @param JsonInput $inputs
     * @param array $rules
     */
    public function __construct(JsonInput $inputs, array $rules)
    {
        $this->inputs   = $inputs;
        $this->rules    = $rules;
    }
}