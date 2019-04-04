<?php

namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Helper\Str;
use Chukdo\Json\Input;

/**
 * Validation de regle.
 * @version   1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since     08/01/2019
 * @author    Domingo Jean-Pierre <jp.domingo@gmail.com>
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
    protected $type = [
        'array' => false,
        'min'   => 0,
        'max'   => 10000,
    ];

    /**
     * @var bool
     */
    protected $isRequired = false;

    /**
     * @var bool
     */
    protected $isForm = false;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * Rule constructor.
     * @param Validator $validator
     * @param string    $path
     * @param string    $rule
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function __construct( Validator $validator, string $path, string $rule )
    {
        $this->path      = trim($path);
        $this->validator = $validator;
        $this->label     = $this->path;

        $this->parseRule($rule);
    }

    /**
     * @param string $rule
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function parseRule( string $rule ): void
    {
        $rules = explode('|',
            $rule);

        foreach( $rules as $rule ) {
            $parsed = $this->parseAttributes($rule);
            $rule   = $parsed[ 'rule' ];
            $attrs  = $parsed[ 'attr' ];

            switch( $rule ) {
                case 'form':
                    $this->isForm = true;
                    break;
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
                        'array' => true,
                        'min'   => $min,
                        'max'   => $max,
                    ];
                    break;
                default:
                    $this->rules[ $rule ] = $attrs;
            }
        }
    }

    /**
     * @param string $rule
     * @return array
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function parseAttributes( string $rule ): array
    {
        list($rule, $attrs) = array_pad(explode(':',
            $rule),
            2,
            '');

        $attrs = strlen($attrs) == 0
            ? []
            : explode(',',
                $attrs);

        /* Recherche d'attribut faisant référence à un chemin de configuration (commence par @) */
        foreach( $attrs as $k => $attr ) {
            if( substr($attr, 0, 1) == '@' ) {
                $attrs[ $k ] = $this->validator->request()
                    ->getConf(substr($attr, 1));
            }
        }

        return [
            'rule' => $rule,
            'attr' => $attrs,
        ];
    }

    /**
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    public function validate(): bool
    {
        if( $this->inputRequired() ) {
            if( $this->inputScalarOrArray() ) {
                $this->inputFilters();
                return $this->validateRule();
            }
        }

        return false;
    }

    /**
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function inputRequired(): bool
    {
        if( $this->input() === null ) {
            if( $this->isRequired ) {
                $this->error('required');
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function inputScalarOrArray(): bool
    {
        $input = $this->input();

        if( $this->type[ 'array' ] ) {
            if( $input instanceof Input ) {
                $countInput = count($input->toSimpleArray());

                if( $countInput >= $this->type[ 'min' ] && $countInput <= $this->type[ 'max' ] ) {
                    return true;
                }
            }

            $this->error('array');
            return false;
        }
        elseif( $input instanceof Input ) {
            $this->error('scalar');
            return false;
        }

        return true;
    }

    /**
     *
     */
    protected function inputFilters(): void
    {
        foreach( $this->rules as $name => $attrs ) {
            if( $filter = $this->validator->filter($name) ) {
                $filter->attributes($attrs);
                $this->inputFilter($filter);
            }
        }
    }

    /**
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function validateRule(): bool
    {
        $validated = true;

        foreach( $this->rules as $name => $attrs ) {
            if( $validate = $this->validator->validator($name) ) {
                $validate->attributes($attrs);
                $validated .= $this->validateInput($validate, $name);
            }
        }

        return $validated;
    }

    /**
     * @return mixed
     */
    protected function input()
    {
        $input = Str::contain($this->path,
            '*')
            ? $this->validator->inputs()
                ->wildcard($this->path,
                    true)
            : $this->validator->inputs()
                ->get($this->path);

        /* Recherche dans file */
        if( $input === null ) {
            $input = $this->validator->inputs()
                ->file($this->path);
        }

        return $input;
    }

    /**
     * @param string      $key
     * @param string|null $path
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function error( string $key, string $path = null ): void
    {
        if( $path ) {
            if( !Str::contain($this->path,
                '*') ) {
                $path = $this->path . '.' . $path;
            }
        }
        else {
            $path = $this->path;
        }

        $this->validator->errors()
            ->offsetSet($path, sprintf($this->validator->message($key), $this->label));
    }

    /**
     * @param FilterInterface $filter
     */
    protected function inputFilter( FilterInterface $filter ): void
    {
        $input = $this->input();

        if( $input instanceof Input ) {
            $input->filterRecursive(function( $k, $v ) use ( $filter ) {
                return $filter->filter($v);
            });

            $this->validator->inputs()
                ->mergeRecursive($input, true);
        }
        else {
            $this->validator->inputs()
                ->set($this->path, $filter->filter($input));
        }
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function validateInput( ValidateInterface $validate, string $name ): bool
    {
        $input = $this->input();

        if( $input instanceof Input ) {
            return $this->validateArray($validate, $name);
        }

        return $this->validateScalar($validate, $name);
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function validateArray( ValidateInterface $validate, string $name ): bool
    {
        $validated = true;
        $input     = $this->input();

        foreach( $input->toSimpleArray() as $k => $v ) {
            if( $validate->validate($v) ) {
                $this->validator->validated()
                    ->set($k, $v);
            }
            elseif( $this->isForm ) {
                $this->error($name, $k);
                $validated .= false;
            }
            else {
                $this->error($name);
                $validated .= false;
                break;
            }
        }

        return $validated;
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     * @return bool
     * @throws \Chukdo\Bootstrap\ServiceException
     * @throws \ReflectionException
     */
    protected function validateScalar( ValidateInterface $validate, string $name ): bool
    {
        $validated = true;
        $input     = $this->input();

        if( $validate->validate($input) ) {
            $this->validator->validated()
                ->set($this->path, $input);
        }
        else {
            $this->error($name);
            $validated .= false;
        }

        return $validated;
    }
}
