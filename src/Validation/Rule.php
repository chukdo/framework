<?php namespace Chukdo\Validation;

/**
 * Validation de regle
 *
 * @package     Validation
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Rule
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $rule;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var
     */
    protected $input;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Rule constructor.
     *
     * @param string $name
     * @param string $rule
     * @param string $message
     * @param $input
     * @param array $attributes
     */
    public function __construct( string $name, string $rule, string $message, $input, array $attributes = [] )
    {
        $this->name       = trim( $name );
        $this->rule       = trim( $rule );
        $this->input      = $input;
        $this->message    = $message;
        $this->attributes = array_map(
            'trim',
            $attributes
        );
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function rule(): string
    {
        return $this->rule;
    }

    /**
     * @return mixed
     */
    public function input()
    {
        return $this->input;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}