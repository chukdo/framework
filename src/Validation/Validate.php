<?php namespace Chukdo\Validation;

use Chukdo\Contracts\Validation\Validate as ValidateInterface;
use Chukdo\Json\Input;

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
     * @param Input $inputs
     *
     * @return bool
     */
    public function validate( Rule $rule, Input $inputs ): bool
    {

    }
}