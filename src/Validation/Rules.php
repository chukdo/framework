<?php namespace Chukdo\Validation;

use Chukdo\Json\Arr;
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
     */
    public function __construct( Iterable $rules )
    {
        $this->rules = new Arr();

        foreach ( $rules as $name => $rule ) {
            $this->rules->merge( $this->parseRules( $name, $rule ) );
        }
    }

    /**
     * @param string $name
     * @param string $rules
     *
     * @return Arr
     */
    protected function parseRules( string $name, string $rules ): Arr
    {
        $parseRules = new Arr();

        foreach ( explode( '|', $rules ) as $rule ) {
            $parseRules->append( $this->parseRule( $name, $rule ) );
        }

        return $parseRules;
    }

    /**
     * @param string $name
     * @param string $rule
     *
     * @return Rule
     */
    protected function parseRule( string $name, string $rule ): Rule
    {
        $ruleItems = explode( ':', $rule );
        $countItems = count( $ruleItems );

        switch ( $countItems ) {
            case 1 :
                return new Rule( $name, $ruleItems[ 0 ] );
            case 2 :
                return new Rule( $name, $ruleItems[ 0 ], explode( ',', $ruleItems[ 1 ] ) );
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