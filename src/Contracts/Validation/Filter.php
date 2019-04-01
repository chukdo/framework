<?php

namespace Chukdo\Contracts\Validation;

use Chukdo\Validation\Rule;

/**
 * Interface des regles de validation.
 *
 * @version    1.0.0
 * @copyright    licence MIT, Copyright (C) 2019 Domingo
 * @since        08/01/2019
 * @author        Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
interface Filter
{
    /**
     * @return string
     */
    public function name(): string;

    /**
     * @param array $attributes
     * @return self
     */
    public function attributes(array $attributes): self;

    /**
     *
     * @param $input
     * @param Rule $rule
     * @return bool
     */
    public function filter($input, Rule $rule): bool;
}
