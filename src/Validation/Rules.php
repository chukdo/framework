<?php namespace Chukdo\Validation;

use Chukdo\Json\Arr;
use Chukdo\Json\Input;
use Chukdo\View\ValidationException;
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
     * Rules constructor.
     *
     * @param iterable $rules
     * @param Input $inputs
     */
    public function __construct( Iterable $rules, Input $inputs )
    {
        $this->rules = new Arr();

        foreach ( $rules as $name => $rule ) {
            $this->rules->merge( $this->parseRules( $name, $rule , $inputs->wildcard($name) ) );
        }
    }

    /**
     * @param string $name
     * @param string $rules
     * @param $input
     *
     * @return Arr
     */
    protected function parseRules( string $name, string $rules, $input ): Arr
    {
        $parseRules = new Arr();

        foreach ( explode( '|', $rules ) as $rule ) {
            $parseRules->append( $this->parseRule( $name, $rule, $input ) );
        }

        return $parseRules;
    }

    /**
     * @param string $name
     * @param string $rule
     * @param $input
     *
     * @return Rule
     */
    protected function parseRule( string $name, string $rule, $input ): Rule
    {
        $ruleItems = explode( ':', $rule );
        $countItems = count( $ruleItems );

        switch ( $countItems ) {
            case 1 :
                return new Rule( $name, $ruleItems[ 0 ], $input );
            case 2 :
                return new Rule( $name, $ruleItems[ 0 ], $input, explode( ',', $ruleItems[ 1 ] ) );
        }

        throw new ValidationException( sprintf( 'Rule [%s] format error', $rule ) );
    }

    /**
     * @return Arr|\Traversable
     */
    public function getIterator()
    {
        return $this->rules;
    }
}