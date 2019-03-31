<?php

namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use Chukdo\Json\Message;

/**
 * Validation de données.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator
{
    /**
     * @var Input
     */
    protected $validated;

    /**
     * @var Rules
     */
    protected $rules;

    /**
     * @var array
     */
    protected $validate = [];

    /**
     * @var Message
     */
    protected $error;

    /**
     * Validator constructor.
     *
     * @param Input $inputs
     * @param array $rules
     * @param Lang  $messages
     */
    public function __construct(Input $inputs, array $rules, Lang $messages)
    {
        $this->error = new Message('error');
        $this->rules = new Rules(
            $rules,
            $inputs,
            $messages
        );
        $this->validated = new Input([]);
    }

    /**
     * @param ValidateInterface $validate
     *
     * @return Validator
     */
    public function register(ValidateInterface $validate): self
    {
        $this->validate[$validate->name()] = $validate;

        return $this;
    }

    /**
     * @return Input
     */
    public function validated(): Input
    {
        return $this->validated;
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return $this->error->count() > 0;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $validate = true;

        foreach ($this->rules() as $rule) {
            $validate .= $this->validateRule($rule);
        }

        return $validate;
    }

    /**
     * @param Rule $rule
     *
     * @return bool
     */
    public function validateRule(Rule $rule): bool
    {
        if (in_array($rule->rule(), ['array', 'scalar']) || !is_iterable($rule->input())) {
            return $this->validateInput(
                $rule,
                $rule->input()
            );
        }

        $validate = true;

        foreach ($rule->input() as $input) {
            $validate .= $this->validateInput(
                $rule,
                $input
            );
        }

        return $validate;
    }

    /**
     * @param Rule $rule
     * @param $input
     *
     * @return bool
     */
    public function validateInput(Rule $rule, $input): bool
    {
        if (!isset($this->validate[$rule->rule()])) {
            throw new ValidationException(
                sprintf(
                    'Validation Rule [%s] does not exist',
                    $rule->rule()
                )
            );
        }

        $validate = $this->validate[$rule->rule()]->validate(
            $input,
            $rule->attributes()
        );

        if ($validate === true) {
            $this->validated->offsetSet($rule->name(), $rule->input());

            return true;
        }

        $this->error->offsetSet($rule->name(), $rule->message());

        return false;
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
    public function errors(): Message
    {
        return $this->error;
    }
}
