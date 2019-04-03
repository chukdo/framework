<?php

namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Http\Request;
use Chukdo\Json\Input;
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
     * @var Request
     */
    protected $request;

    /**
     * @var Input
     */
    protected $inputs;

    /**
     * @var Input
     */
    protected $validated;

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
     * @param Request $request
     */
    public function __construct( Request $request )
    {
        $this->request   = $request;
        $this->error     = new Message('error');
        $this->validated = new Input([]);
        $this->inputs    = $request->inputs();
    }

    /**
     * @param array $rules
     * @return Validator
     */
    public function registerRules( array $rules ): self
    {
        foreach( $rules as $path => $rule ) {
            $this->rules[] = new Rule($this, $path, $rule);
        }

        return $this;
    }

    /**
     * @param array $validators
     * @return Validator
     */
    public function registerValidators( array $validators ): self
    {
        foreach ($validators as $validator) {
            $this->registerValidator($validator);
        }

        return $this;
    }

    /**
     * @param ValidateInterface $validator
     * @return Validator
     */
    public function registerValidator( ValidateInterface $validator ): self
    {
        $this->validators[ $validator->name() ] = $validator;

        return $this;
    }

    /**
     * @param array $filters
     * @return Validator
     */
    public function registerFilters( array $filters ): self
    {
        foreach ($filters as $filter) {
            $this->registerFilter($filter);
        }

        return $this;
    }

    /**
     * @param FilterInterface $filter
     * @return Validator
     */
    public function registerFilter( FilterInterface $filter ): self
    {
        $this->filters[ $filter->name() ] = $filter;

        return $this;
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

        foreach( $this->rules() as $rule ) {
            $validate .= $rule->validate();
        }

        return $validate;
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->rules;
    }

    /**
     * @param string $filter
     * @return FilterInterface|null
     */
    public function filter( string $filter ): ?FilterInterface
    {
        if( isset($this->filters[ $filter ]) ) {
            return $this->filters[ $filter ];
        }

        return null;
    }

    /**
     * @return array
     */
    public function filters(): array
    {
        return $this->filters;
    }

    /**
     * @param string $validator
     * @return ValidateInterface|null
     */
    public function validator( string $validator ): ?ValidateInterface
    {
        if( isset($this->validators[ $validator ]) ) {
            return $this->validators[ $validator ];
        }

        return null;
    }

    /**
     * @return array
     */
    public function validators(): array
    {
        return $this->validators;
    }

    /**
     * @return Input
     */
    public function validated(): Input
    {
        return $this->validated;
    }

    /**
     * @return Input
     */
    public function inputs(): Input
    {
        return $this->inputs;
    }

    /**
     * @return Message
     */
    public function errors(): Message
    {
        return $this->error;
    }

    /**
     * @param string $key
     * @return string
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function message( string $key ): string
    {
        return $this->request->getLang($key, sprintf('Validation message [%s] cannot be found', $key));
    }
}
