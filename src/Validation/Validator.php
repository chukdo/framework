<?php namespace Chukdo\Validation;

use Chukdo\Json\JsonInput;
use Chukdo\Json\JsonLang;

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
    protected $messages = [];

    /**
     * Validator constructor.
     * @param JsonInput $inputs
     * @param array $rules
     * @param JsonLang $messages
     */
    public function __construct(JsonInput $inputs, array $rules, JsonLang $messages)
    {
        $this->inputs   = $inputs;
        $this->rules    = $rules;
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @return array
     */
    public function messages(): array
    {
        return $this->messages;
    }

    public function register()
    {
        // objet
        // getRules()
    }

    public function validate()
    {
        /**
        each $rules as $path => $rule
         * explode(| $rule)
         * input->wildcard($path)
         * check call function issu d'un tableau de fonction registreed
        */
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