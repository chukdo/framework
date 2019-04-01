<?php

namespace Chukdo\Validation;

use Chukdo\Helper\Str;
use Chukdo\Json\Arr;
use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use IteratorAggregate;

/**
 * Validation des regles.
 *
 * @version    1.0.0
 *
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 *
 * @since        08/01/2019
 *
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Rules implements IteratorAggregate
{
    /**
     * @var Arr
     */
    protected $rules;

    /**
     * @var Lang
     */
    protected $messages;

    /**
     * Rules constructor.
     *
     * @param iterable $rules
     * @param Input    $inputs
     * @param Lang     $messages
     */
    public function __construct(Iterable $rules, Input $inputs, Lang $messages)
    {
        $this->rules    = new Arr();
        $this->messages = $messages;

        foreach ($rules as $name => $rulesPiped) {
            if (Str::contain($name, ':')) {
                list($name, $label) = explode(':', $name);
            } else {
                $label = $name;
            }

            $input = $inputs->get($name);

            if (Str::contain($name, '*')) {
                $input = $inputs->wildcard($name);
            }

            $this->rules->merge(
                $this->parseRules(
                    $name,
                    $label,
                    $rulesPiped,
                    $input
                )
            );
        }
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $rulesPiped
     * @param $input
     *
     * @return Arr
     */
    protected function parseRules(string $name, string $label, string $rulesPiped, $input): Arr
    {
        $parseRules = new Arr();
        $rules      = explode('|', $rulesPiped);

        /* Defini input par défaut comme un scalaire */
        if (Str::notContain($rulesPiped, 'array')) {
            array_unshift($rules, 'scalar');
        }

        foreach ($rules as $rule) {
            $parseRules->append(
                $this->parseRule(
                    $name,
                    $label,
                    $rule,
                    $input
                )
            );
        }

        return $parseRules;
    }

    /**
     * @param string $name
     * @param string $label
     * @param string $ruleColon
     * @param $input
     *
     * @return Rule
     */
    protected function parseRule(string $name, string $label, string $ruleColon, $input): Rule
    {
        list($rule, $param) = array_pad(explode(':', $ruleColon), 2, '');

        $param   = $param == '' ? [] : explode(',', $param);
        $message = $this->messages->offsetGetFirstInList(
            [
                $label,
                $name . '.' . $rule,
                $name,
                $rule,
            ],
            sprintf(
                'Validation message [%s:%s] cannot be found',
                $rule,
                $label
            )
        );

        return new Rule(
            $name,
            $label,
            $rule,
            $message,
            $input,
            $param
        );
    }

    /**
     * @return Arr|\Traversable
     */
    public function getIterator()
    {
        return $this->rules;
    }
}
