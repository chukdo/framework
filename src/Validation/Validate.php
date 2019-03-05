<?php namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\View\ValidationException;

/**
 * Validation de données
 *
 * @package     Validation
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validate
{
    /**
     * @var array
     */
    protected $validate = [];

    /**
     * @var Validator
     */
    protected $validator;

    /**
     * Validate constructor.
     *
     * @param Validator $validator
     */
    public function __construct( Validator $validator )
    {
        $this->validator = $validator;
    }

    /**
     * @param ValidateInterface $validate
     *
     * @return Validate
     */
    public function register( ValidateInterface $validate ): self
    {
        $this->validate[ $validate->name() ] = $validate;

        return $this;
    }

    /**
     * @param Rule $rule
     */
    public function validate( Rule $rule )
    {
        // cas rule->input() = array (multiple)

        // isset validate $rule->name();
        // call user func
        // ko => validator->error()
        // $this->error->offsetSet( $rule->field(), $this->messageFromRule( $rule ) );
        // ok =>
    }

    /**
     * @param Rule $rule
     *
     * @return string
     */
    public function messageFromRule( Rule $rule ): string
    {
        $messages = $this->validator->messages();
        $message  = $messages->offsetGet( $rule->namespace() ) ?: $messages->offsetGet( $rule->name() );

        if ( $message ) {
            return $message;
        }

        throw new ValidationException( sprintf( 'Message [%s] cannot be found', $rule->name() ) );
    }
}