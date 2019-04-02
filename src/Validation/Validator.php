<?php

namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use Chukdo\Json\Message;

/**
 * Validation de données.
 * @version      1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author       Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator
{
    /**
     * @var Input
     */
    protected $inputs;

    /**
     * @var Input
     */
    protected $validated;

    /**
     * @var Lang
     */
    protected $messages;

    /**
     * @var Message
     */
    protected $error;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * Validator constructor.
     * @param Input $inputs
     * @param array $rules
     * @param Lang  $messages
     */
    public function __construct( Input $inputs, array $rules, Lang $messages ) {
        $this->error     = new Message('error');
        $this->inputs    = $inputs->clone();
        $this->messages  = $messages;
        $this->validated = new Input([]);

        foreach( $rules as $path => $rule ) {
            $this->rules[] = new Rule($path, $rule, $this);
        }
    }

    /**
     * @param ValidateInterface $validate
     * @return Validator
     */
    public function registerValidator( ValidateInterface $validate ): self {
        $this->validators[ $validate->name() ] = $validate;

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return Validator
     */
    public function registerFilter( FilterInterface $filter ): self {
        $this->filters[ $filter->name() ] = $filter;

        return $this;
    }

    /**
     * @return bool
     */
    public function fails(): bool {
        return $this->error->count() > 0;
    }

    /**
     * @return bool
     */
    public function validate(): bool {
        $validate = true;

        foreach( $this->rules() as $rule ) {
            $validate .= $rule->validate();
        }

        return $validate;
    }

    /**
     * @return array
     */
    public function rules(): array {
        return $this->rules;
    }

    /**
     * @param string $filter
     * @return FilterInterface|null
     */
    public function filter( string $filter ): ?FilterInterface {
        if( isset($this->filters[ $filter ]) ) {
            return $this->filters[ $filter ];
        }

        return null;
    }

    /**
     * @return array
     */
    public function filters(): array {
        return $this->filters;
    }

    /**
     * @param string $validator
     * @return ValidateInterface|null
     */
    public function validator( string $validator ): ?ValidateInterface {
        if( isset($this->validators[ $validator ]) ) {
            return $this->validators[ $validator ];
        }

        return null;
    }

    /**
     * @return array
     */
    public function validators(): array {
        return $this->validators;
    }

    /**
     * @return Input
     */
    public function validated(): Input {
        return $this->validated;
    }

    /**
     * @return Input
     */
    public function inputs(): Input {
        return $this->inputs;
    }

    /**
     * @return Message
     */
    public function errors(): Message {
        return $this->error;
    }

    /**
     * @param array $listName
     * @return string
     */
    public function message( array $listName ): string {
        return $this->messages->offsetGetFirstInList($listName,
            sprintf('Validation message [%s] cannot be found',
                implode(', ',
                    $listName)));
    }
}
