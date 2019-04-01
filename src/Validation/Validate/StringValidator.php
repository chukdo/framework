<?php

namespace Chukdo\Validation\Validate;

use Chukdo\Contracts\Validation\Validate;
use Chukdo\Validation\Rule;

/**
 * Validate handler.
 *
 * @version 1.0.0
 * @copyright licence MIT, Copyright (C) 2019 Domingo
 * @since 08/01/2019
 * @author Domingo Jean-Pierre <jp.domingo@gmail.com>
 */
class StringValidator implements Validate
{
    /**
     * @var int
     */
    protected $min = 0;

    /**
     * @var int
     */
    protected $max = 1000000;

    /**
     * @return string
     */
    public function name(): string
    {
        return 'string';
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function attributes(array $attributes): Validate
    {
        $attributes = array_pad($attributes, 2, 0);
        $this->min  = $attributes[0];
        $this->max  = $attributes[1] ?: $attributes[0] ?: pow(10, 6);

        return $this;
    }

    /**
     * @param $input
     * @param Rule $rule
     *
     * @return bool
     */
    public function validate($input, Rule $rule): bool
    {
        if (is_string($input)) {
            $len = strlen($input);

            if ($len >= $this->min && $len <= $this->max) {
                return true;
            }
        }

        return false;
    }
}
