<?php

namespace Chukdo\Validation;

use Chukdo\Bootstrap\ServiceException;
use Chukdo\Contracts\Http\Input as InputInterface;
use Chukdo\Contracts\Validation\Filter as FilterInterface;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Helper\Str;
use Chukdo\Json\Json;
use ReflectionException;

/**
 * Validation de regle.
 *
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
    protected string $path;

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var Validator
     */
    protected Validator $validator;

    /**
     * @var string
     */
    protected string $label;

    /**
     * @var array
     */
    protected array $type = [ 'array' => false,
                              'min'   => 0,
                              'max'   => 10000, ];

    /**
     * @var bool
     */
    protected bool $isRequired = false;

    /**
     * @var bool
     */
    protected bool $isForm = false;

    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * Rule constructor.
     *
     * @param Validator $validator
     * @param string    $path
     * @param string    $rule
     */
    public function __construct( Validator $validator, string $path, string $rule )
    {
        $this->path      = trim( $path );
        $this->validator = $validator;
        $this->label     = $this->path;
        $this->parseRule( $rule );
    }

    /**
     * @param string $rule
     */
    protected function parseRule( string $rule ): void
    {
        $rules = explode( '|', $rule );

        foreach ( $rules as $ruleItem ) {
            [ $ruleName,
              $attrs ] = $this->parseAttributes( $ruleItem );

            switch ( $ruleName ) {
                case 'form':
                    $this->isForm = true;
                    break;
                case 'required':
                    $this->isRequired = true;
                    break;
                case 'label':
                    $this->label = $attrs->get( 0 );
                    break;
                case 'array':
                    $this->type = [ 'array' => true,
                                    'min'   => $attrs->get( 0, 0 ),
                                    'max'   => $attrs->get( 1, $attrs->get( 0, 10000 ) ), ];
                    break;
                default:
                    $this->rules[ $ruleName ] = (array)$attrs;
            }
        }
    }

    /**
     * @param string $rule
     *
     * @return array
     */
    protected function parseAttributes( string $rule ): array
    {
        [ $rule,
          $attrs ] = Str::explode( ':', $rule, 2 );
        $json = new Json( Str::explode( ',', $attrs ) );

        /** Recherche d'attributs faisant référence à un chemin de configuration (commence par @) */
        $filter = $json->filter( function( $k, $v )
        {
            $isConf = strpos( $v, '@' ) === 0;
            $conf   = substr( $v, 1 );

            if ( $isConf ) {
                return $this->validator->request()
                                       ->conf( $conf );
            }

            return $v;
        } );

        return [ $rule,
                 $filter, ];
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if ( $this->inputRequired() && $this->inputScalarOrArray() ) {
            $this->inputFilters();

            return $this->validateRule();
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function inputRequired(): bool
    {
        if ( $this->isRequired && ( $this->input() === null ) ) {
            $this->error( 'required' );

            return false;
        }

        return true;
    }

    /**
     * @return mixed
     */
    protected function input()
    {
        $input = Str::contain( $this->path, '*' )
            ? $this->validator->inputs()
                              ->wildcard( $this->path, true )
            : $this->validator->inputs()
                              ->get( $this->path );

        /** Recherche dans file */
        if ( $input === null ) {
            $input = $this->validator->inputs()
                                     ->file( $this->path );
        }

        return $input;
    }

    /**
     * @param string      $key
     * @param string|null $path
     */
    protected function error( string $key, string $path = null ): void
    {
        if ( $path ) {
            if ( !Str::contain( $this->path, '*' ) ) {
                $path = $this->path . '.' . $path;
            }
        } else {
            $path = $this->path;
        }
        $this->validator->errors()
                        ->offsetSet( $path, sprintf( $this->validator->message( $key ), $this->label ) );
    }

    /**
     * @return bool
     */
    protected function inputScalarOrArray(): bool
    {
        $input = $this->input();

        if ( $input instanceof InputInterface ) {
            if ( $this->type[ 'array' ] ) {
                $countInput = count( $input->toSimpleArray() );

                if ( $countInput >= $this->type[ 'min' ] && $countInput <= $this->type[ 'max' ] ) {
                    return true;
                }

                $this->error( 'array' );
            } else {
                $this->error( 'scalar' );
            }

            return false;
        }

        return true;
    }

    /**
     *
     */
    protected function inputFilters(): void
    {
        foreach ( $this->rules as $name => $attrs ) {
            if ( $filter = $this->validator->filter( $name ) ) {
                $filter->attributes( $attrs );
                $this->inputFilter( $filter );
            }
        }
    }

    /**
     * @param FilterInterface $filter
     */
    protected function inputFilter( FilterInterface $filter ): void
    {
        $input = $this->input();
        if ( $input instanceof InputInterface ) {
            $inputs = $input->filterRecursive( fn( $k, $v ) => $filter->filter( $v ) );
            $this->validator->inputs()
                            ->mergeRecursive( $inputs, true );
        } else {
            $this->validator->inputs()
                            ->set( $this->path, $filter->filter( $input ) );
        }
    }

    /**
     * @return bool
     */
    protected function validateRule(): bool
    {
        $validated = true;
        foreach ( $this->rules as $name => $attrs ) {
            if ( $validate = $this->validator->validator( $name ) ) {
                $validate->attributes( $attrs );
                $validated .= $this->validateInput( $validate, $name );
            }
        }

        return $validated;
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     *
     * @return bool
     */
    protected function validateInput( ValidateInterface $validate, string $name ): bool
    {
        $input = $this->input();

        if ( $input instanceof InputInterface ) {
            return $this->validateArray( $validate, $name );
        }

        return $this->validateScalar( $validate, $name );
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     *
     * @return bool
     */
    protected function validateArray( ValidateInterface $validate, string $name ): bool
    {
        $validated = true;
        $input     = $this->input();

        foreach ( $input->toSimpleArray() as $k => $v ) {
            if ( $validate->validate( $v ) ) {
                $this->validator->validated()
                                ->set( $k, $v );
            } elseif ( $this->isForm ) {
                $this->error( $name, $k );
                $validated .= false;
            } else {
                $this->error( $name );
                $validated .= false;
                break;
            }
        }

        return $validated;
    }

    /**
     * @param ValidateInterface $validate
     * @param string            $name
     *
     * @return bool
     */
    protected function validateScalar( ValidateInterface $validate, string $name ): bool
    {
        $validated = true;
        $input     = $this->input();

        if ( $validate->validate( $input ) ) {
            $this->validator->validated()
                            ->set( $this->path, $input );
        } else {
            $this->error( $name );
            $validated .= false;
        }

        return $validated;
    }
}
