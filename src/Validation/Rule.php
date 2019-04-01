<?php

namespace Chukdo\Validation;

use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use Chukdo\Validation\Validator;

/**
 * Validation de regle.
 *
 * @version 1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since 08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Rule
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Json
     */
    protected $type;

    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var array
     */
    protected $validatorsAndFilters = [];

    /**
     * Constructor.
     *
     * @param string $path
     * @param string $rule
     * @param Validator $validator
     */
    public function __construct(string $path, string $rule, Validator $validator)
    {
        $this->path      = trim($path);
        $this->validator = $validator;

        $this->setLabel($this->path);
        $this->setType(false);
        $this->parseRule($rule);
    }

    /**
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * @return Json
     */
    public function type(): Json
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function input(string $name)
    {
        if (Str::contain($name, '*')) {
            $input = $this->validator->inputs()->wildcard($name);
        } else {
            $input = $this->validator->inputs()->get($name);
        }

        return $input;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $input = $this->input($this->path());

        if ($this->isRequired() && $input === null) {
            $this->validator->errors()->offsetSet($this->path(), $this->validator->message(['required']));
            return false;
        }

        return true;
    }

    /**
     * @param string $rule
     * @return void
     */
    protected function parseRule(string $rule): void
    {
        $rules = explode('|', $rule);

        foreach ($rules as $rule) {
            $parsed = $this->parseAttribute($rule);
            $rule   = $parsed->rule;
            $attrs  = $parsed->attr;

            switch ($rule) {
                case 'required':
                    $this->isRequired = true;
                    break;
                case 'label':
                    $this->setLabel($attrs->offsetGet(0));
                    break;
                case 'array':
                    $this->setType(true, $attrs->offsetGet(0, 0), $attrs->offsetGet(1, 10000));
                    break;
                default:
                    $this->setValidatorAndFilter($rule, $attrs);
            }
        }
    }

    /**
     * @param string $rule
     * @param \Chukdo\Json\Json $attr
     * @return void
     */
    protected function setValidatorAndFilter(string $rule, Json $attr): void
    {
        $this->validatorsAndFilters[] = [
            'name' => $rule,
            'attr' => $attr,
        ];
    }

    /**
     * @param string $label
     * @return void
     */
    protected function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @param bool $isArray
     * @param int $min
     * @param int $max
     * @return void
     */
    protected function setType(bool $isArray, int $min = 0, int $max = 10000): void
    {
        $this->type = new Json([
            'array' => $isArray,
            'min'   => $min,
            'max'   => $max,
        ]);
    }

    /**
     * @param string $rule
     * @return \Chukdo\Json\Json
     */
    protected function parseAttribute(string $rule): Json
    {
        list($rule, $attributes) = array_pad(explode(':', $rule), 2, '');

        return new Json([
            'rule' => $rule,
            'attr' => explode(',', $attributes),
        ]);
    }
}
