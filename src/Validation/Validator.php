<?php namespace Chukdo\Validation;

use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Json\Message;

/**
 * Validation de données
 *
 * @package     Validation
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class Validator
{
    /**
     * @var Input
     */
    protected $validated;

    /**
     * @var Input
     */
    protected $inputs;

    /**
     * @var Rules
     */
    protected $rules;

    /**
     * @var Validate
     */
    protected $validate;

    /**
     * @var Lang
     */
    protected $messages;

    /**
     * @var Message
     */
    protected $error;

    /**
     * Validator constructor.
     *
     * @param Input $inputs
     * @param array $rules
     * @param Lang $messages
     */
    public function __construct( Input $inputs, array $rules, Lang $messages )
    {
        $this->inputs   = $inputs;
        $this->messages = $messages;
        $this->error    = new Message( 'error' );
        $this->rules    = new Rules( $rules, $inputs );
        $this->validate = new Validate($this);
    }

    /**
     * @param ValidateInterface $validate
     *
     * @return Validator
     */
    public function register( ValidateInterface $validate ): self
    {
        $this->validate->register( $validate );

        return $this;
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->validated();
    }

    /**
     * @return Input
     */
    public function validated(): Input
    {
        if ( $this->validated === null ) {
            $this->validate();
        }

        return $this->validated;
    }

    /**
     * @return Input
     */
    public function validate(): Input
    {
        $r = true;

        foreach ( $this->rules() as $rule ) {

            if ( !$this->validate->validate( $rule ) ) {

                $r .= false;
            }
        }

        return $r;
    }

    /**
     * @return Rules
     */
    public function rules(): Rules
    {
        return $this->rules;
    }

    /**
     * @return Input
     */
    public function inputs(): Input
    {
        return $this->inputs;
    }

    /**
     * @return Lang
     */
    public function messages(): Lang
    {
        return $this->messages;
    }

    /**
     * @return Message
     */
    public function errors(): Message
    {
        return $this->error;
    }
}