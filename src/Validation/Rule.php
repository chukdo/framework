<?php

namespace Chukdo\Validation;

use Chukdo\Helper\Str;
use Chukdo\Json\Input;
use Chukdo\Json\Json;

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
     * @var array
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
    public function __construct( string $path, string $rule, Validator $validator )
    {
        $this->path      = trim($path);
        $this->validator = $validator;

        $this->setLabel($this->path);
        $this->setType(false);
        $this->parseRule($rule);
    }

    /**
     * @param string $label
     *
     * @return void
     */
    protected function setLabel( string $label ): void
    {
        $this->label = $label;
    }

    /**
     * @param bool $isArray
     * @param int $min
     * @param int $max
     *
     * @return void
     */
    protected function setType( bool $isArray, int $min = 0, int $max = 10000 ): void
    {
        $this->type = [
            'array' => $isArray,
            'min'   => $min,
            'max'   => $max,
        ];
    }

    /**
     * @param string $rule
     * @param array $attr
     */
    protected function setValidatorAndFilter( string $rule, array $attr ): void
    {
        $this->validatorsAndFilters[ $rule ] = $attr;
    }

    /**
     * @return mixed
     */
    protected function input()
    {
        return Str::contain(
            $this->path,
            '*'
        )
            ? $this->validator->inputs()->wildcard($this->path)
            : $this->validator->inputs()->get($this->path);
    }

    /**
     * @param string $message
     *
     * @return void
     */
    protected function error( string $message ): void
    {
        $this->validator->errors()->offsetSet(
            $this->path,
            $message
        );
    }

    /**
     * @param array $listName
     *
     * @return string
     */
    protected function message( array $listName ): string
    {
        return sprintf(
            $this->validator->message($listName),
            $this->label
        );
    }

    /**
     * @param string $rule
     */
    protected function parseRule( string $rule ): void
    {
        $rules = explode(
            '|',
            $rule
        );

        foreach( $rules as $rule ) {
            $parsed = $this->parseAttribute($rule);
            $rule   = $parsed[ 'rule' ];
            $attrs  = $parsed[ 'attr' ];

            switch( $rule ) {
                case 'required':
                    $this->isRequired = true;
                    break;
                case 'label':
                    $this->setLabel($attrs[ 0 ]);
                    break;
                case 'array':
                    $this->setType(
                        true,
                        isset($attrs[ 0 ])
                            ? $attrs[ 0 ]
                            : 0,
                        isset($attrs[ 1 ])
                            ? $attrs[ 0 ]
                            : 10000
                    );
                    break;
                default:
                    $this->setValidatorAndFilter(
                        $rule,
                        $attrs
                    );
            }
        }
    }

    /**
     * @param string $rule
     *
     * @return array
     */
    protected function parseAttribute( string $rule ): array
    {
        list($rule, $attributes) = array_pad(
            explode(
                ':',
                $rule
            ),
            2,
            ''
        );

        return [
            'rule' => $rule,
            'attr' => strlen($attributes) == 0
                ? []
                : explode(
                    ',',
                    $attributes
                ),
        ];
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $input = $this->input();

        if( !$this->validateRequired($input) ) {
            return false;
        }

        if( !$this->validateType($input) ) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $input
     *
     * @return bool
     */
    protected function validateRequired( $input ): bool
    {
        if( $input === null ) {
            if( $this->isRequired ) {
                $this->error($this->message([ 'required' ]));
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $input
     *
     * @return bool
     */
    protected function validateType( $input ): bool
    {
        if( $this->type[ 'array' ] ) {
            if( $input instanceof Input ) {
                $countInput = $input->count();

                if( $countInput >= $this->type[ 'min' ] && $countInput <= $this->type[ 'max' ] ) {
                    return true;
                }
            }

            $this->error($this->message([ 'array' ]));
            return false;
        } else if( is_iterable($input) ) {
            $this->error($this->message([ 'scalar' ]));
            return false;
        }

        return true;
    }

    /**
     * @param mixed $input
     *
     * @return bool
     */
    protected function validateValidators( $input ): bool
    {
    }

    /**
     * @param mixed $input
     *
     * @return bool
     */
    protected function validateFilters( $input ): bool
    {
        foreach( $this->validatorsAndFilters as $name => $filter ) {
            if( $filter = $this->validator->filter($name) ) {
                unset($this->validatorsAndFilters[ $name ]);

                $filter->filter($input);
            }
        }
    }
}
