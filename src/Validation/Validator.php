<?php namespace Chukdo\Validation;

use Chukdo\Json\Input;
use Chukdo\Json\Lang;
use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Json\Message;
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
    protected $validate = [];

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
        $this->error     = new Message( 'error' );
        $this->rules     = new Rules( $rules, $inputs, $messages );
        $this->validated = new Input( [] );
    }

    /**
     * @param ValidateInterface $validate
     *
     * @return Validator
     */
    public function register( ValidateInterface $validate ): self
    {
        $this->validate[ $validate->name() ] = $validate;

        return $this;
    }

    /**
     * @return Input
     */
    public function validated(): Input
    {
        return $this->validated;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        foreach ( $this->rules() as $rule ) {
            // cas rule->input() = array (multiple)

            // required ok
            // array ok
            // int ok (loop var) car wildcard !!! solution

            // isset validate $rule->name();
            // call user func
            // ko => validator->error()
            // $this->error->offsetSet( $rule->field(), $this->messageFromRule( $rule ) );
            // ok =>
        }

        return true;
    }

    /**
     * @return string
     */
    public function field(): string
    {
        // account.mail => champ account[mail] to.*.email => to[0][email] et to[1][email] -> field
        // to.*.email => email pour message comment l'associer ?!
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