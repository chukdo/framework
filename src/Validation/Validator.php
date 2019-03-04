<?php namespace Chukdo\Validation;

use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use Chukdo\Json\Message;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;

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
     * @var bool
     */
    protected $validated = null;

    /**
     * @var Input
     */
    protected $inputs;

    /**
     * @var Rules
     */
    protected $rules;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * @var Message
     */
    protected $message;

    /**
     * Validator constructor.
     * @param Input $inputs
     * @param array $rules
     * @param Lang $messages
     */
    public function __construct(Input $inputs, array $rules, Lang $messages)
    {
        $this->validate = new Validate();
        $this->rules    = new Rules($inputs, $rules, $messages, $this->validate);
        $this->message  = new Message();
    }

    /**
     * @return Rules
     */
    public function rules(): Rules
    {
        return $this->rules;
    }

    /**
     * @return Message
     */
    public function messages(): Message
    {
        return $this->message;
    }

    /**
     * @param ValidateInterface $validate
     * @return Validator
     */
    public function register(ValidateInterface $validate): self
    {
        $this->validate->register($validate);

        return $this;
    }

    public function validate()
    {
        foreach ($this->rules() as $rule) {
            if (!$rule->validate()) {
                $this->message->error($rule->getMessage());
            }
        }
    }

    /**
     * @return bool
     */
    public function validated(): bool
    {
        if ($this->validated === null) {
            $this->validate();
        }

        return $this->validated;
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->validated();
    }

    /**
     * @return Message
     */
    public function errors(): Message
    {
        return $this->message->offsetGet('messages');
    }
}