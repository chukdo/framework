<?php

namespace Chukdo\Validation;

use Chukdo\Helper\Str;
use Chukdo\Json\Input;

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
    protected $type
        = [
            false,
            0,
            10000
        ];

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
        $this->label     = $this->path;

        $this->parseRule($rule);
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
            ? $this->validator->inputs()->wildcard(
                $this->path,
                true
            )
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
                    $this->label = $attrs[ 0 ];
                    break;
                case 'array':
                    $min        = isset($attrs[ 0 ])
                        ? $attrs[ 0 ]
                        : 0;
                    $max        = isset($attrs[ 1 ])
                        ? $attrs[ 1 ]
                        : ($min
                            ?: 10000);
                    $this->type = [
                        true,
                        $min,
                        $max
                    ];
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

        $this->validateFilters($input);

        return $this->validateValidators($input);
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
                $countInput = count($input->toSimpleArray());

                if( $countInput >= $this->type[ 'min' ] && $countInput <= $this->type[ 'max' ] ) {
                    return true;
                }
            }

            $this->error($this->message([ 'array' ]));
            return false;
        } else if( $input instanceof Input ) {
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
        $validated = true;

        foreach( $this->validatorsAndFilters as $name => $attrs ) {
            if( $validate = $this->validator->validator($name) ) {
                $validate->attributes($attrs);

                if( $input instanceof Input ) {
                    foreach( $input->toSimpleArray() as $k => $v ) {
                        if( $validate->validate($v) ) {
                            $this->validator->validated()->set(
                                $k,
                                $v
                            );
                        } else {
                            $this->error($this->message([ $name ]));
                            $validated .= false;
                            break;
                        }
                    }
                } else if( $validate->validate($input) ) {
                    $this->validator->validated()->set(
                        $this->path,
                        $input
                    );
                } else {
                    $this->error($this->message([ $name ]));
                    $validated .= false;
                }
            }
        }

        return $validated;
    }

    /**
     * @param $input
     */
    protected function validateFilters( $input ): void
    {
        foreach( $this->validatorsAndFilters as $name => $attrs ) {
            if( $filter = $this->validator->filter($name) ) {
                $filter->attributes($attrs);

                unset($this->validatorsAndFilters[ $name ]);

                if( $input instanceof Input ) {
                    $input->filterRecursive(
                        function( $k, $v ) use ( $filter ) {
                            return $filter->filter($v);
                        }
                    );

                    $this->validator->inputs()->mergeRecursive(
                        $input,
                        true
                    );
                } else {
                    $this->validator->inputs()->set(
                        $this->path,
                        $filter->filter($input)
                    );
                }
            }
        }
    }
}
