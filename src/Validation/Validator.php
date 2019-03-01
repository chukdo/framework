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
     * @var array
     */
    protected $messages = [
        'required'  => 'Le champs' // gestion des langues ?
    ];

    /**
     * Validator constructor.
     * @param JsonInput $inputs
     * @param array $rules
     * @param array $messages
     */
    public function __construct(JsonInput $inputs, array $rules, array $messages = [])
    {
        $this->inputs   = $inputs;
        $this->rules    = $rules;
        $this->messages = array_merge($this->messages, $messages);
    }

    public function validate()
    {

    }

    public function validated()
    {

    }

    public function fails()
    {

    }

    public function errors()
    {
        // sous la forme d'un json
    }
}