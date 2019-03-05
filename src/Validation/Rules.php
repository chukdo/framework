<?php namespace Chukdo\Validation;

use Chukdo\Json\Arr;
use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use IteratorAggregate;

/**
 * Validation des regles
 *
 * @package     Validation
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
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
     * @param Input $inputs
     * @param Lang $messages
     */
    public function __construct( Iterable $rules, Input $inputs, Lang $messages )
    {
        $this->rules = new Arr();
        $this->messages = $messages;

        foreach ( $rules as $name => $rulesPiped ) {
            $this->rules->merge(
                $this->parseRules(
                    $name,
                    $rulesPiped,
                    $inputs->wildcard( $name )
                )
            );
        }
    }

    /**
     * @param string $name
     * @param string $rulesPiped
     * @param $input
     *
     * @return Arr
     */
    protected function parseRules( string $name, string $rulesPiped, $input ): Arr
    {
        $parseRules = new Arr();

        foreach ( explode(
            '|',
            $rulesPiped
        ) as $rule ) {
            $parseRules->append(
                $this->parseRule(
                    $name,
                    $rule,
                    $input
                )
            );
        }

        return $parseRules;
    }

    /**
     * @param string $name
     * @param string $ruleColon
     * @param $input
     *
     * @return Rule
     */
    protected function parseRule( string $name, string $ruleColon, $input ): Rule
    {
        list( $rule, $param ) = array_pad(
            explode(
                ':',
                $ruleColon
            ),
            2,
            ''
        );

        $message = $this->messages->offsetGetFirstInList(
            [
                $name,
                $name . '.' . $rule
            ],
            sprintf(
                'Validation message [%s] cannot be found',
                $name
            )
        );

        return new Rule(
            $name,
            $rule,
            $message,
            $input,
            explode(
                ',',
                $param
            )
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