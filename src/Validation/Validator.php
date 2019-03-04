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
     * @var bool
     */
    protected $validated = null;

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
        $this->inputs = $inputs;
        $this->messages = $messages;
        $this->validate = new Validate();
        $this->rules = new Rules( $rules );
        $this->error = new Message( 'error' );
    }

    /**
     * @return Input
     */
    public function inputs(): Input
    {
        return $this->inputs;
    }

    /**
     * @return Rules
     */
    public function rules(): Rules
    {
        return $this->rules;
    }

    /**
     * @return Lang
     */
    public function messages(): Lang
    {
        return $this->messages;
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
    public function validate(): bool
    {
        $r = true;

        foreach ( $this->rules() as $rule ) {
            if ( !$this->validate->validate( $rule, $this->inputs(), $this->messages() ) ) {
                $r .= false;
            }
        }

        // error $this->messages()->get($rule->namespace()) || $this->messages()->get($rule->name()) || throw exception(message no exist)
        // validate => passe error
        // rule.name() => correspondance en erreur dans this->inputs !!!

        //$this->error->offsetSet($rule->name(), $this->messages()->get());
        // $rule->rule() == int / required account.price / account.mail => champ account[mail] to.*.email => to[0][email] et to[1][email]

        return $r;
    }

    /**
     * @return bool
     */
    public function validated(): bool
    {
        if ( $this->validated === null ) {
            $this->validate();
        }

        return $this->validated;
    }

    /**
     * @return bool
     */
    public function fails(): bool
    {
        return !$this->validated();
    }

    /**
     * @return Message
     */
    public function errors(): Message
    {
        return $this->error;
    }
}